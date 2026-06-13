<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use App\Models\AssessmentQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SebConfigController extends Controller
{
    /**
     * Show the SEB-required page when a student tries to access
     * an SEB-required assessment from a normal browser.
     */
    public function sebRequired($questionId)
    {
        $question = AssessmentQuestion::active()->findOrFail($questionId);

        if (!$question->isSebRequired()) {
            return redirect()->route('student.assessment.show', $questionId);
        }

        return view('student.assessment.seb-required', compact('question'));
    }

    /**
     * Generate and download a .seb configuration file for a specific assessment.
     *
     * The .seb file is a plist (XML) file that Safe Exam Browser reads to configure
     * itself for the exam. It locks down the browser, restricts URLs, and enforces
     * exam settings.
     */
    public function downloadConfig($questionId)
    {
        $question = AssessmentQuestion::active()->findOrFail($questionId);

        if (!$question->isSebEnabled()) {
            return redirect()->route('student.assessment.show', $questionId)
                ->with('error', 'This assessment does not use Safe Exam Browser.');
        }

        $user = Auth::user();
        $assessmentUrl = route('student.assessment.show', $questionId);
        $configKey = $question->seb_config_key ?: $this->generateConfigKey($question);

        $sebConfig = $this->generateSebPlist($question, $assessmentUrl, $configKey);

        $filename = sprintf('Redemption_SEB_%s_%s.seb',
            Str::slug($question->subject->name ?? 'assessment'),
            $questionId
        );

        return response($sebConfig)
            ->header('Content-Type', 'application/seb')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Generate a SEB configuration key for a question.
     * This is a random 64-character hex string that both the .seb config
     * and the server know, allowing header-based verification.
     */
    private function generateConfigKey(AssessmentQuestion $question): string
    {
        $configKey = bin2hex(random_bytes(32));
        $question->update(['seb_config_key' => $configKey]);
        return $configKey;
    }

    /**
     * Generate the .seb plist (XML) configuration file.
     *
     * The .seb format is an XML plist with specific keys that SEB reads.
     * Documentation: https://safeexambrowser.org/developer/seb-plist-format.html
     *
     * We generate the XML plist format (not the encrypted format) because
     * it's simpler and doesn't require the SEB admin tools.
     */
    private function generateSebPlist(AssessmentQuestion $question, string $assessmentUrl, string $configKey): string
    {
        $siteUrl = config('app.url');
        $siteHost = parse_url($siteUrl, PHP_URL_HOST) ?? 'localhost';

        // Allowed URLs: the assessment URL + the site root
        $allowedUrls = [$assessmentUrl, $siteUrl];
        if ($question->seb_allowed_urls) {
            $allowedUrls = array_merge($allowedUrls, $question->seb_allowed_urls);
        }
        $allowedUrls = array_unique($allowedUrls);

        // Build URL filter rules
        $urlFilterRules = '';
        foreach ($allowedUrls as $i => $url) {
            $urlFilterRules .= $this->buildUrlFilterRule($i, $url, true);
        }
        // Block all other URLs (active=0 means blocked)
        $urlFilterRules .= $this->buildUrlFilterRule(count($allowedUrls), '.*', false);

        $browserViewMode = $question->seb_browser_view_mode ?? 1;
        $allowQuit = $question->seb_allow_quit ? 'true' : 'false';
        $quitPassword = $question->seb_quit_password ?? '';
        $showTaskbar = $question->seb_show_taskbar ? 'true' : 'false';
        $showTime = $question->seb_show_time ? 'true' : 'false';
        $allowSpellCheck = $question->seb_allow_spell_check ? 'true' : 'false';

        $examName = e('Redemption - ' . ($question->subject->name ?? 'Assessment') . ' - Q' . $question->id);

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
    <key>sebConfigPurpose</key>
    <integer>0</integer>
    <key>examSessionClearCookiesOnStart</key>
    <true/>
    <key>sendBrowserExamKey</key>
    <true/>
    <key>browserExamKey</key>
    <string>{$configKey}</string>
    <key>startURL</key>
    <string>{$assessmentUrl}</string>
    <key>sebMode</key>
    <integer>0</integer>
    <key>examName</key>
    <string>{$examName}</string>
    <key>allowQuit</key>
    <{$allowQuit}/>
    <key>quitURL</key>
    <string></string>
    <key>quitURLConfirm</key>
    <true/>
    <key>quitPassword</key>
    <string>{$quitPassword}</string>
    <key>exitKey1</key>
    <integer>0</integer>
    <key>exitKey2</key>
    <integer>0</integer>
    <key>exitKey3</key>
    <integer>0</integer>
    <key>browserViewMode</key>
    <integer>{$browserViewMode}</integer>
    <key>mainBrowserWindowWidth</key>
    <string>100%</string>
    <key>mainBrowserWindowHeight</key>
    <string>100%</string>
    <key>mainBrowserWindowPositioning</key>
    <integer>1</integer>
    <key>enableBrowserWindowToolbar</key>
    <false/>
    <key>hideBrowserWindowToolbar</key>
    <true/>
    <key>showMenuBar</key>
    <false/>
    <key>showTaskBar</key>
    <{$showTaskbar}/>
    <key>taskBarHeight</key>
    <integer>40</integer>
    <key>showTaskBarClock</key>
    <{$showTime}/>
    <key>showReloadButton</key>
    <true/>
    <key>showTime</key>
    <{$showTime}/>
    <key>showInputLanguage</key>
    <false/>
    <key>enableZoomPage</key>
    <false/>
    <key>enableZoomText</key>
    <false/>
    <key>zoomMode</key>
    <integer>0</integer>
    <key>allowSpellCheck</key>
    <{$allowSpellCheck}/>
    <key>allowDictionaryLookup</key>
    <false/>
    <key>allowZoomOut</key>
    <false/>
    <key>URLFilterEnable</key>
    <true/>
    <key>URLFilterEnableContentFilter</key>
    <false/>
    <key>URLFilterRules</key>
    <array>
{$urlFilterRules}
    </array>
    <key>blockedPopupsRegex</key>
    <string>.*</string>
    <key>allowPopUps</key>
    <false/>
    <key>downloadDirectoryOSX</key>
    <string></string>
    <key>downloadDirectoryWin</key>
    <string></string>
    <key>openDownloads</key>
    <false/>
    <key>chooseFileToUploadPolicy</key>
    <integer>0</integer>
    <key>downloadPDFFiles</key>
    <false/>
    <key>allowPDFPlugIn</key>
    <true/>
    <key>blockPopUpWindows</key>
    <true/>
    <key>allowBrowsingBackForward</key>
    <false/>
    <key>enablePlugIns</key>
    <true/>
    <key>enableJava</key>
    <false/>
    <key>enableJavaScript</key>
    <true/>
    <key>blockPopUpWindows</key>
    <true/>
    <key>allowFlash</key>
    <false/>
    <key>mediaAutoplay</key>
    <integer>0</integer>
    <key>mediaCameraCapture</key>
    <integer>0</integer>
    <key>mediaMicrophoneCapture</key>
    <integer>0</integer>
    <key>allowSwitchToApplications</key>
    <false/>
    <key>allowWindowSwitching</key>
    <false/>
    <key>enablePrintScreen</key>
    <false/>
    <key>enableRightClick</key>
    <false/>
    <key>enableStartMenu</key>
    <false/>
    <key>enableCtrlEsc</key>
    <false/>
    <key>enableAltEsc</key>
    <false/>
    <key>enableAltF4</key>
    <false/>
    <key>enableAltTab</key>
    <false/>
    <key>enableF1F12</key>
    <false/>
    <key>enableFind</key>
    <false/>
    <key>enableSelectionText</key>
    <false/>
    <key>enableCopyPaste</key>
    <false/>
    <key>createNewDesktop</key>
    <true/>
    <key>killExplorerShell</key>
    <false/>
    <key>monitorProcesses</key>
    <true/>
    <key>allowScreenSharing</key>
    <false/>
    <key>enableScreenSharing</key>
    <false/>
    <key>prohibitedProcesses</key>
    <array>
        <dict>
            <key>active</key>
            <true/>
            <key>os</key>
            <integer>0</integer>
            <key>executable</key>
            <string>TeamViewer</string>
        </dict>
        <dict>
            <key>active</key>
            <true/>
            <key>os</key>
            <integer>0</integer>
            <key>executable</key>
            <string>AnyDesk</string>
        </dict>
        <dict>
            <key>active</key>
            <true/>
            <key>os</key>
            <integer>0</integer>
            <key>executable</key>
            <string>Zoom</string>
        </dict>
        <dict>
            <key>active</key>
            <true/>
            <key>os</key>
            <integer>1</integer>
            <key>executable</key>
            <string>TeamViewer</string>
        </dict>
        <dict>
            <key>active</key>
            <true/>
            <key>os</key>
            <integer>1</integer>
            <key>executable</key>
            <string>AnyDesk</string>
        </dict>
        <dict>
            <key>active</key>
            <true/>
            <key>os</key>
            <integer>1</integer>
            <key>executable</key>
            <string>zoom.us</string>
        </dict>
    </array>
</dict>
</plist>
XML;
    }

    /**
     * Build a URL filter rule entry for the SEB plist.
     */
    private function buildUrlFilterRule(int $index, string $url, bool $allowed): string
    {
        $active = $allowed ? 'true' : 'false';
        $action = $allowed ? '1' : '0'; // 1=allow, 0=block
        $escapedUrl = e($url);

        return <<<XML
        <dict>
            <key>action</key>
            <integer>{$action}</integer>
            <key>active</key>
            <{$active}/>
            <key>expression</key>
            <string>{$escapedUrl}</string>
            <key>regex</key>
            <{$active}/>
        </dict>
XML;
    }
}
