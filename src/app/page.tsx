'use client'

import { useState, useRef, useEffect, useCallback } from 'react'

const SCHOOL_URL = 'https://schoolofredemption.net'

export default function Home() {
  const [url, setUrl] = useState(SCHOOL_URL)
  const [currentUrl, setCurrentUrl] = useState(SCHOOL_URL)
  const [loading, setLoading] = useState(true)
  const [showBar, setShowBar] = useState(false)
  const [canGoBack, setCanGoBack] = useState(false)
  const [canGoForward, setCanGoForward] = useState(false)
  const [history, setHistory] = useState<string[]>([SCHOOL_URL])
  const [historyIndex, setHistoryIndex] = useState(0)
  const [showMenu, setShowMenu] = useState(false)
  const iframeRef = useRef<HTMLIFrameElement>(null)

  // Handle navigation
  const navigate = useCallback((targetUrl: string) => {
    const formatted = targetUrl.startsWith('http') ? targetUrl : `${SCHOOL_URL}/${targetUrl.replace(/^\//, '')}`
    setUrl(formatted)
    setCurrentUrl(formatted)
    setLoading(true)
  }, [])

  // Go back
  const goBack = useCallback(() => {
    if (historyIndex > 0) {
      const newIndex = historyIndex - 1
      setHistoryIndex(newIndex)
      navigate(history[newIndex])
      setCanGoBack(newIndex > 0)
      setCanGoForward(newIndex < history.length - 1)
    }
  }, [history, historyIndex, navigate])

  // Go forward
  const goForward = useCallback(() => {
    if (historyIndex < history.length - 1) {
      const newIndex = historyIndex + 1
      setHistoryIndex(newIndex)
      navigate(history[newIndex])
      setCanGoBack(newIndex > 0)
      setCanGoForward(newIndex < history.length - 1)
    }
  }, [history, historyIndex, navigate])

  // Reload
  const reload = useCallback(() => {
    setLoading(true)
    if (iframeRef.current) {
      iframeRef.current.src = currentUrl
    }
  }, [currentUrl])

  // Handle URL submit
  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    let target = url.trim()
    if (!target.startsWith('http')) {
      target = `${SCHOOL_URL}/${target.replace(/^\//, '')}`
    }
    const newHistory = [...history.slice(0, historyIndex + 1), target]
    setHistory(newHistory)
    setHistoryIndex(newHistory.length - 1)
    setCanGoBack(true)
    setCanGoForward(false)
    navigate(target)
  }

  // Print current page
  const handlePrint = useCallback(() => {
    try {
      if (iframeRef.current?.contentWindow) {
        iframeRef.current.contentWindow.focus()
        iframeRef.current.contentWindow.print()
      } else {
        window.print()
      }
    } catch {
      // Cross-origin fallback — open in new tab for printing
      window.open(currentUrl, '_blank')
    }
  }, [currentUrl])

  // Export/share current page
  const handleExport = useCallback(() => {
    if (navigator.share) {
      navigator.share({
        title: 'School of Redemption',
        url: currentUrl,
      }).catch(() => {})
    } else {
      // Copy URL to clipboard
      navigator.clipboard?.writeText(currentUrl).then(() => {
        alert('Link copied to clipboard')
      }).catch(() => {
        window.open(currentUrl, '_blank')
      })
    }
  }, [currentUrl])

  // Open in external browser
  const handleOpenExternal = useCallback(() => {
    window.open(currentUrl, '_blank')
    setShowMenu(false)
  }, [currentUrl])

  // Handle iframe load
  const handleLoad = () => {
    setLoading(false)
    try {
      const iframe = iframeRef.current
      if (iframe?.contentWindow) {
        const newUrl = iframe.contentWindow.location.href
        if (newUrl && newUrl !== 'about:blank' && newUrl !== currentUrl) {
          setCurrentUrl(newUrl)
          setUrl(newUrl)
        }
      }
    } catch {
      // Cross-origin — can't read URL, that's OK
    }
  }

  // Keyboard shortcuts
  useEffect(() => {
    const handler = (e: KeyboardEvent) => {
      if (e.altKey && e.key === 'ArrowLeft') { e.preventDefault(); goBack() }
      if (e.altKey && e.key === 'ArrowRight') { e.preventDefault(); goForward() }
      if (e.ctrlKey && e.key === 'r') { e.preventDefault(); reload() }
      if (e.ctrlKey && e.key === 'p') { e.preventDefault(); handlePrint() }
    }
    window.addEventListener('keydown', handler)
    return () => window.removeEventListener('keydown', handler)
  }, [goBack, goForward, reload, handlePrint])

  // Detect mobile
  const [isMobile, setIsMobile] = useState(false)
  useEffect(() => {
    const check = () => setIsMobile(window.innerWidth < 768)
    check()
    window.addEventListener('resize', check)
    return () => window.removeEventListener('resize', check)
  }, [])

  return (
    <div className="fixed inset-0 flex flex-col bg-[#047857] overflow-hidden" style={{ height: '100dvh' }}>
      {/* ── Top Bar ── */}
      <div className="flex items-center gap-2 px-3 py-2 bg-gradient-to-r from-emerald-700 to-teal-600 text-white shadow-lg z-20">
        {/* Back */}
        <button
          onClick={goBack}
          disabled={!canGoBack}
          className="p-2 rounded-lg hover:bg-white/15 disabled:opacity-30 transition-colors touch-manipulation"
          aria-label="Back"
        >
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
            <path d="M15 18l-6-6 6-6" />
          </svg>
        </button>

        {/* Forward */}
        <button
          onClick={goForward}
          disabled={!canGoForward}
          className="p-2 rounded-lg hover:bg-white/15 disabled:opacity-30 transition-colors touch-manipulation"
          aria-label="Forward"
        >
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
            <path d="M9 18l6-6-6-6" />
          </svg>
        </button>

        {/* URL Bar */}
        <form onSubmit={handleSubmit} className="flex-1 flex items-center bg-white/15 rounded-full px-3 py-1.5 backdrop-blur-sm">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="mr-2 opacity-70 flex-shrink-0">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
          </svg>
          <input
            type="text"
            value={url.replace(/^https?:\/\//, '').split('/')[0] + (url.replace(/^https?:\/\/[^/]+/, '') || '/')}
            onChange={(e) => {
              const path = e.target.value
              setUrl(path.startsWith('http') ? path : `${SCHOOL_URL}${path.startsWith('/') ? '' : '/'}${path}`)
            }}
            className="flex-1 bg-transparent text-white text-sm outline-none placeholder-white/50 min-w-0"
            placeholder="Search or enter URL"
            onFocus={() => setShowBar(true)}
            onBlur={() => setTimeout(() => setShowBar(false), 200)}
            size={1}
          />
          <button type="button" onClick={reload} className="ml-2 p-1 rounded-full hover:bg-white/15 transition-colors flex-shrink-0" aria-label="Reload">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" className={loading ? 'animate-spin' : ''}>
              <path d="M23 4v6h-6" />
              <path d="M1 20v-6h6" />
              <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15" />
            </svg>
          </button>
        </form>

        {/* Menu */}
        <button
          onClick={() => setShowMenu(!showMenu)}
          className="p-2 rounded-lg hover:bg-white/15 transition-colors touch-manipulation"
          aria-label="Menu"
        >
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
            <circle cx="12" cy="12" r="1" />
            <circle cx="12" cy="5" r="1" />
            <circle cx="12" cy="19" r="1" />
          </svg>
        </button>
      </div>

      {/* ── Dropdown Menu ── */}
      {showMenu && (
        <>
          <div className="fixed inset-0 z-30" onClick={() => setShowMenu(false)} />
          <div className="absolute right-3 top-14 z-40 bg-white rounded-xl shadow-2xl border border-gray-200 overflow-hidden min-w-[200px]">
            <button onClick={handlePrint} className="w-full flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition-colors text-left">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#047857" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                <polyline points="6 9 6 2 18 2 18 9" />
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                <rect x="6" y="14" width="12" height="8" />
              </svg>
              <span className="text-sm font-medium text-gray-700">Print Page</span>
            </button>
            <button onClick={handleExport} className="w-full flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition-colors text-left">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#047857" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                <polyline points="7 10 12 15 17 10" />
                <line x1="12" y1="15" x2="12" y2="3" />
              </svg>
              <span className="text-sm font-medium text-gray-700">Share / Export</span>
            </button>
            <button onClick={handleOpenExternal} className="w-full flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition-colors text-left">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#047857" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
                <polyline points="15 3 21 3 21 9" />
                <line x1="10" y1="14" x2="21" y2="3" />
              </svg>
              <span className="text-sm font-medium text-gray-700">Open in Browser</span>
            </button>
            <div className="border-t border-gray-100" />
            <button onClick={() => { navigate(SCHOOL_URL); setShowMenu(false) }} className="w-full flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition-colors text-left">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#047857" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                <polyline points="9 22 9 12 15 12 15 22" />
              </svg>
              <span className="text-sm font-medium text-gray-700">Home</span>
            </button>
          </div>
        </>
      )}

      {/* ── Loading Bar ── */}
      {loading && (
        <div className="h-0.5 bg-emerald-600 relative overflow-hidden">
          <div className="absolute inset-0 bg-white/60 animate-pulse" style={{ width: '40%', animation: 'loadingbar 1.5s ease-in-out infinite' }} />
          <style>{`@keyframes loadingbar { 0% { left: -40% } 50% { left: 50% } 100% { left: 100% } }`}</style>
        </div>
      )}

      {/* ── Webview (iframe) ── */}
      <div className="flex-1 relative bg-white overflow-hidden">
        <iframe
          ref={iframeRef}
          src={currentUrl}
          onLoad={handleLoad}
          className="w-full h-full border-0"
          title="School of Redemption"
          sandbox="allow-same-origin allow-scripts allow-forms allow-popups allow-popups-to-escape-sandbox allow-downloads allow-modals"
          referrerPolicy="no-referrer-when-downgrade"
          allow="fullscreen; camera; microphone; geolocation; clipboard-read; clipboard-write"
        />
        {loading && (
          <div className="absolute inset-0 flex items-center justify-center bg-white">
            <div className="flex flex-col items-center gap-4">
              <div className="relative w-16 h-16">
                <div className="absolute inset-0 rounded-full border-4 border-emerald-200" />
                <div className="absolute inset-0 rounded-full border-4 border-emerald-600 border-t-transparent animate-spin" />
              </div>
              <p className="text-sm text-gray-500 font-medium">Loading School of Redemption...</p>
            </div>
          </div>
        )}
      </div>

      {/* ── Bottom Navigation (mobile only) ── */}
      {isMobile && (
        <div className="flex items-center justify-around bg-white border-t border-gray-200 px-2 py-1.5 shadow-lg z-20" style={{ paddingBottom: 'max(0.375rem, env(safe-area-inset-bottom))' }}>
          <button onClick={() => navigate(SCHOOL_URL)} className="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-lg active:bg-gray-100 transition-colors touch-manipulation">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#047857" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
              <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
              <polyline points="9 22 9 12 15 12 15 22" />
            </svg>
            <span className="text-[10px] font-medium text-gray-600">Home</span>
          </button>
          <button onClick={goBack} disabled={!canGoBack} className="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-lg active:bg-gray-100 transition-colors disabled:opacity-30 touch-manipulation">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#047857" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
              <path d="M15 18l-6-6 6-6" />
            </svg>
            <span className="text-[10px] font-medium text-gray-600">Back</span>
          </button>
          <button onClick={handlePrint} className="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-lg active:bg-gray-100 transition-colors touch-manipulation">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#047857" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
              <polyline points="6 9 6 2 18 2 18 9" />
              <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
              <rect x="6" y="14" width="12" height="8" />
            </svg>
            <span className="text-[10px] font-medium text-gray-600">Print</span>
          </button>
          <button onClick={handleExport} className="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-lg active:bg-gray-100 transition-colors touch-manipulation">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#047857" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
              <polyline points="7 10 12 15 17 10" />
              <line x1="12" y1="15" x2="12" y2="3" />
            </svg>
            <span className="text-[10px] font-medium text-gray-600">Share</span>
          </button>
          <button onClick={() => navigate(SCHOOL_URL + '/login')} className="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-lg active:bg-gray-100 transition-colors touch-manipulation">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#047857" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
              <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
              <polyline points="10 17 15 12 10 7" />
              <line x1="15" y1="12" x2="3" y2="12" />
            </svg>
            <span className="text-[10px] font-medium text-gray-600">Login</span>
          </button>
        </div>
      )}
    </div>
  )
}
