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
    }
};
