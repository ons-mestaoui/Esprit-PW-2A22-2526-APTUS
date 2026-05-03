window.AIAgentUtils = {
    // Fill a text input or textarea
    fillField: function(selector, value) {
        const el = document.querySelector(selector);
        if (el) {
            el.value = value;
            // Trigger input/change events for frameworks/listeners
            el.dispatchEvent(new Event('input', { bubbles: true }));
            el.dispatchEvent(new Event('change', { bubbles: true }));
            return true;
        }
        return false;
    },

    // Click an element
    clickElement: function(selector) {
        const el = document.querySelector(selector);
        if (el) {
            el.click();
            return true;
        }
        return false;
    },

    // Set value for a select element
    setSelect: function(selector, value) {
        const el = document.querySelector(selector);
        if (el) {
            el.value = value;
            el.dispatchEvent(new Event('change', { bubbles: true }));
            return true;
        }
        return false;
    },

    // Specific for Aptus Veille Admin: Add a tag
    addSecteurTag: function(tag) {
        if (typeof window.addTag === 'function') {
            window.addTag(tag);
            return true;
        }
        return false;
    },

    // Specific for Aptus Veille Admin: Fill Quill editor
    setQuillContent: function(html) {
        if (typeof quill !== 'undefined') {
            quill.root.innerHTML = html;
            if (typeof updateLivePreview === 'function') updateLivePreview();
            return true;
        }
        return false;
    },

    // Specific for Aptus Veille Admin: Go to step
    goToStep: function(step) {
        // The page defines goToStep(step) globally
        if (typeof window.goToStep === 'function') {
            window.goToStep(step);
            return true;
        }
        return false;
    },

    // Specific for Aptus Veille Admin: Edit report
    editReport: function(index) {
        const btns = document.querySelectorAll('.rapport-item .btn-ghost[onclick*="openRapportModal(\'edit\'"]');
        if (btns && btns[index]) {
            btns[index].click();
            return true;
        }
        return false;
    },

    // Specific for Aptus Veille Admin: Delete report
    deleteReport: function(index) {
        const btns = document.querySelectorAll('.rapport-item .text-danger[onclick*="openDeleteModal"]');
        if (btns && btns[index]) {
            btns[index].click();
            return true;
        }
        return false;
    },

    // Specific for Aptus Veille Public: Read report
    readReport: function(index) {
        const links = document.querySelectorAll('.report-card a[href*="veille_details.php"]');
        if (links && links[index]) {
            links[index].click();
            return true;
        }
        return false;
    },

    // Specific for Aptus Veille Public: Export PDF
    
    // Specific for Aptus Veille Public: Trigger Flash Briefing
    triggerFlashBriefing: function(reportId, title) {
        if (!window.AIAssistant) return false;
        
        window.AIAssistant.updatePowerState(true);
        window.AIAssistant.setState('loading', 'Briefing...');
        
        // Construct the prompt for the AI
        const text = "Fais-moi un briefing audio très court (3 phrases maximum) du rapport intitulé: " + title;
        window.AIAssistant.processUserRequest(text);
        return true;
    }
,

    exportPDF: function() {
        if (typeof window.exportToPDF === 'function') {
            window.exportToPDF();
            return true;
        }
        const btn = document.querySelector('.btn-pdf[onclick="exportToPDF()"]');
        if (btn) {
            btn.click();
            return true;
        }
        return false;
    }
};
