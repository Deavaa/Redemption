/**
 * Filter Persistence + Default Selection Script
 * ----------------------------------------------------------------------------
 * Automatically:
 * 1. Defaults academic_year_id and term_id <select> elements to the current
 *    academic year / term when no value is selected via URL parameters.
 * 2. Remembers the user's previous filter selections (per page) in
 *    localStorage and restores them on next visit.
 * 3. Saves filter selections to localStorage when the filter form is submitted.
 *
 * This script is loaded on all admin pages via the admin layout.
 * It only affects <select> elements with name="academic_year_id" or
 * name="term_id" — other filters are handled generically by saving all
 * form filter values keyed by the current page path.
 *
 * No server-side changes needed — the defaults are injected client-side
 * before the form is submitted, so controllers see the correct values.
 */
(function() {
    'use strict';

    var STORAGE_KEY_PREFIX = 'redemption_filters_';
    var pageKey = STORAGE_KEY_PREFIX + window.location.pathname;

    // Current AY / Term defaults injected from server (set via meta tags or
    // data attributes on the <select> elements). We read them from a global
    // JS variable set by the admin layout.
    var defaultAyId = window.REDEMPTION_DEFAULTS ? window.REDEMPTION_DEFAULTS.academic_year_id : 0;
    var defaultTermId = window.REDEMPTION_DEFAULTS ? window.REDEMPTION_DEFAULTS.term_id : 0;

    /**
     * Check if the current URL has a query parameter (meaning the user
     * already applied a filter). If so, we don't override their selection.
     */
    function hasUrlParams() {
        return window.location.search.length > 1;
    }

    /**
     * Get a saved filter value from localStorage.
     */
    function getSavedFilter(name) {
        try {
            var data = JSON.parse(localStorage.getItem(pageKey) || '{}');
            return data[name] || null;
        } catch (e) {
            return null;
        }
    }

    /**
     * Save all filter values to localStorage.
     */
    function saveFilters(form) {
        try {
            var data = JSON.parse(localStorage.getItem(pageKey) || '{}');
            var selects = form.querySelectorAll('select[name]');
            selects.forEach(function(sel) {
                if (sel.name && sel.value) {
                    data[sel.name] = sel.value;
                }
            });
            // Also save text inputs that look like filters
            var inputs = form.querySelectorAll('input[type="text"][name], input[type="search"][name]');
            inputs.forEach(function(inp) {
                if (inp.name && inp.value) {
                    data[inp.name] = inp.value;
                }
            });
            localStorage.setItem(pageKey, JSON.stringify(data));
        } catch (e) {
            // localStorage might be full or disabled — silently ignore
        }
    }

    /**
     * Apply defaults to a <select> element:
     * 1. If the select already has a value (from old() or URL param), keep it.
     * 2. If not, try the saved value from localStorage.
     * 3. If no saved value, try the server default (current AY/Term).
     */
    function applyDefaults(select) {
        // If the select already has a non-empty selected value, leave it
        // (this happens when the controller sets it via old() or request())
        if (select.value && select.value !== '') return;

        var name = select.name;
        var savedValue = getSavedFilter(name);
        var defaultValue = null;

        if (name === 'academic_year_id') {
            defaultValue = defaultAyId;
        } else if (name === 'term_id') {
            defaultValue = defaultTermId;
        }

        // Try saved value first, then default
        var valueToUse = savedValue || defaultValue;
        if (valueToUse) {
            // Check if the option exists in the select
            var option = select.querySelector('option[value="' + valueToUse + '"]');
            if (option) {
                select.value = valueToUse;
                // Trigger change event so any cascade JS (e.g. AY→Term fetch) runs
                select.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
    }

    /**
     * Initialize on DOM ready.
     */
    function init() {
        // Only apply defaults if there are NO URL parameters (meaning the user
        // navigated to the page fresh, not via a filter submit). If URL params
        // exist, the form was submitted with specific values — respect them.
        var skipDefaults = hasUrlParams();

        // Find all <select> elements with name containing academic_year_id or term_id
        var filterSelects = document.querySelectorAll('select[name="academic_year_id"], select[name="term_id"]');

        filterSelects.forEach(function(select) {
            if (!skipDefaults) {
                applyDefaults(select);
            }
        });

        // Also handle other filter selects (branch_id, class_id, section_id, etc.)
        // — restore from localStorage only (no server default)
        if (!skipDefaults) {
            var otherSelects = document.querySelectorAll(
                'select[name="branch_id"], select[name="class_id"], select[name="section_id"], ' +
                'select[name="teacher_id"], select[name="subject_id"], select[name="status"], ' +
                'select[name="category"], select[name="exam_id"]'
            );
            otherSelects.forEach(function(select) {
                if (!select.value || select.value === '') {
                    var saved = getSavedFilter(select.name);
                    if (saved) {
                        var opt = select.querySelector('option[value="' + saved + '"]');
                        if (opt) {
                            select.value = saved;
                            select.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    }
                }
            });
        }

        // Hook into filter form submissions to save values
        // Find forms that contain filter selects
        var filterForms = new Set();
        document.querySelectorAll('select[name="academic_year_id"], select[name="term_id"], select[name="branch_id"]').forEach(function(sel) {
            var form = sel.closest('form');
            if (form) filterForms.add(form);
        });

        filterForms.forEach(function(form) {
            form.addEventListener('submit', function() {
                saveFilters(form);
            });
        });
    }

    // Run on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
