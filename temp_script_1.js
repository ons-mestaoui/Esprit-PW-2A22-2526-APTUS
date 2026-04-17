
function switchTab(tabId) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    
    document.querySelector(`button[onclick="switchTab('${tabId}')"]`).classList.add('active');
    document.getElementById('tab-' + tabId).classList.add('active');
}

function closeModals() {
    document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('active'));
}

function openRapportModal(type, data = null) {
    const isEdit = (type === 'edit');
    document.getElementById('rapport-modal-title').innerText = isEdit ? 'Modifier le Rapport' : 'Nouveau Rapport';
    document.getElementById('rapport-action').value = isEdit ? 'update_rapport' : 'add_rapport';
    
    // Fill basic fields
    document.getElementById('rapport-id').value = data?.id_rapport_marche || '';
    document.getElementById('rapport-date').value = data?.date_publication || '';
    document.getElementById('rapport-titre').value = data?.titre || '';
    document.getElementById('rapport-desc').value = data?.description || '';
    document.getElementById('rapport-region').value = data?.region || '';
    document.getElementById('rapport-secteur').value = data?.secteur_principal || '';
    document.getElementById('rapport-smin').value = data?.salaire_min_global || '';
    document.getElementById('rapport-smoy').value = data?.salaire_moyen_global || '';
    document.getElementById('rapport-smax').value = data?.salaire_max_global || '';
    document.getElementById('rapport-tendance').value = data?.tendance_generale || 'Hausse';
    document.getElementById('rapport-demande').value = data?.niveau_demande_global || '3';
    document.getElementById('rapport-auteur').value = data?.auteur || '';
    document.getElementById('rapport-vues-field').value = data?.vues || 0;

    // Rich Text & Image handling
    const uploadZone = document.getElementById('rapport-upload-zone');
    if (isEdit) {
        quill.root.innerHTML = data?.contenu_detaille || '';
        if (data?.image_couverture) {
            document.getElementById('rapport-image-preview').src = data.image_couverture;
            document.getElementById('rapport-image-preview').style.display = 'block';
            document.getElementById('rapport-image-base64').value = data.image_couverture;
            if (uploadZone) uploadZone.classList.add('has-image');
        } else {
            document.getElementById('rapport-image-preview').style.display = 'none';
            if (uploadZone) uploadZone.classList.remove('has-image');
        }
    } else {
        quill.root.innerHTML = '';
        document.getElementById('rapport-image-preview').style.display = 'none';
        document.getElementById('rapport-image-base64').value = '';
        if (uploadZone) uploadZone.classList.remove('has-image');
    }

    // Handle Association Checkboxes (M2M)
    let countVis = 0;
    document.querySelectorAll('.data-checkbox-card').forEach(lbl => {
        let chk = lbl.querySelector('input[type="checkbox"]');
        chk.checked = false; 
        lbl.classList.remove('is-selected');
        
        if (isEdit) {
            let idsStr = lbl.getAttribute('data-rapport-ids');
            if(idsStr) {
                let ids = JSON.parse(idsStr);
                if (ids.includes(parseInt(data.id_rapport_marche)) || ids.includes(data.id_rapport_marche)) {
                    chk.checked = true;
                    lbl.classList.add('is-selected');
                }
            }
        }
        lbl.style.display = 'flex';
        countVis++;
    });

    document.getElementById('no-data-assoc').style.display = countVis === 0 ? 'block' : 'none';

    // Reset Stepper
    let firstErrorElement = document.querySelector('#form-rapport .is-invalid');
    if(firstErrorElement) {
        let stepParent = firstErrorElement.closest('.step-content');
        if(stepParent) {
            goToStep(parseInt(stepParent.id.replace('step-rapport-', '')));
        } else {
            goToStep(1);
        }
    } else {
        goToStep(1);
    }

    document.getElementById('modal-rapport').classList.add('active');
}

// Stepper Logic
let currentRapportStep = 1;
const totalRapportSteps = 4;

function goToStep(step) {
    currentRapportStep = step;
    
    // Update contents
    document.querySelectorAll('.step-content').forEach((el, index) => {
        el.classList.toggle('active', index + 1 === step);
    });
    
    // Update headers
    document.querySelectorAll('.stepper-step').forEach((el, index) => {
        const s = index + 1;
        el.classList.toggle('active', s === step);
        el.classList.toggle('completed', s < step);
    });
    
    // Buttons
    document.getElementById('btn-prev-step').style.display = step > 1 ? 'block' : 'none';
    if(step === totalRapportSteps) {
        document.getElementById('btn-next-step').style.display = 'none';
        document.getElementById('btn-submit-rapport').style.display = 'block';
    } else {
        document.getElementById('btn-next-step').style.display = 'block';
        document.getElementById('btn-submit-rapport').style.display = 'none';
    }
}

function nextStep() {
    if(validateStep(currentRapportStep)) {
        goToStep(currentRapportStep + 1);
    }
}

function prevStep() {
    if(currentRapportStep > 1) {
        goToStep(currentRapportStep - 1);
    }
}

function validateStep(step) {
    let isValid = true;
    
    // Clear previous invalid UI
    document.querySelectorAll(`#step-rapport-${step} .input, #step-rapport-${step} .textarea`).forEach(el => {
        el.classList.remove('is-invalid');
    });

    if (step === 1) {
        const titre = document.getElementById('rapport-titre');
        const auteur = document.getElementById('rapport-auteur');
        if(!titre.value.trim()) { titre.classList.add('is-invalid'); isValid = false; }
        if(!auteur.value.trim()) { auteur.classList.add('is-invalid'); isValid = false; }
    } else if (step === 2) {
        const contenu = quill.root.innerHTML.trim();
        if(contenu === '<p><br></p>' || contenu === '') {
            document.getElementById('editor-container').classList.add('is-invalid');
            isValid = false;
        } else {
            document.getElementById('editor-container').classList.remove('is-invalid');
        }
    } else if (step === 3) {
        const smin = document.getElementById('rapport-smin');
        const smax = document.getElementById('rapport-smax');
        if(!smin.value || isNaN(smin.value)) { smin.classList.add('is-invalid'); isValid = false; }
        if(!smax.value || isNaN(smax.value)) { smax.classList.add('is-invalid'); isValid = false; }
        if(isValid && Number(smin.value) > Number(smax.value)) {
            smax.classList.add('is-invalid'); 
            isValid = false;
        }
    }
    
    return isValid;
}

function validateDataSelection(checkbox) {
    checkbox.parentElement.classList.toggle('is-selected', checkbox.checked);
    document.getElementById('assoc-error').style.display = 'none';
}

function syncQuill() {
    document.getElementById('rapport-contenu').value = quill.root.innerHTML;
    // Step 4 final server-side pre-validation
    const checked = document.querySelectorAll('input[name="linked_donnees[]"]:checked').length;
    if (checked === 0) {
        document.getElementById('assoc-error').style.display = 'block';
        return false;
    }
    return true;
}

function openDonneeModal(type, data = null) {
    const isEdit = (type === 'edit');
    document.getElementById('donnee-modal-title').innerText = isEdit ? 'Modifier la donnée' : 'Ajouter une donnée';
    document.getElementById('donnee-action').value = isEdit ? 'update_donnee' : 'add_donnee';
    
    document.getElementById('donnee-id').value = data?.id_donnee || '';
    document.getElementById('donnee-domaine').value = data?.domaine || '';
    document.getElementById('donnee-competence').value = data?.competence || '';
    document.getElementById('donnee-smin').value = data?.salaire_min || '';
    document.getElementById('donnee-smoy').value = data?.salaire_moyen || '';
    document.getElementById('donnee-smax').value = data?.salaire_max || '';
    document.getElementById('donnee-demande').value = data?.demande || '3';
    document.getElementById('donnee-date').value = data?.date_collecte || '';
    document.getElementById('donnee-desc').value = data?.description || '';
    
    document.getElementById('modal-donnee').classList.add('active');
}

// Preview image and convert to base64
function previewImage(input) {
    if (input.files && input.files[0]) {
        if (input.files[0].size > 5 * 1024 * 1024) {
            alert("L'image est trop lourde (max 5 Mo)");
            input.value = "";
            return;
        }
        var reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('rapport-image-preview');
            preview.src = e.target.result;
            preview.style.display = 'block';
            document.getElementById('rapport-image-base64').value = e.target.result;
            document.getElementById('rapport-upload-zone').classList.add('has-image');
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Initializing Quill
var quill;
document.addEventListener('DOMContentLoaded', function() {
    quill = new Quill('#editor-container', {
        theme: 'snow',
        placeholder: 'Rédigez le contenu détaillé du rapport ici...',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'color': [] }, { 'background': [] }],
                ['link', 'blockquote', 'code-block'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['clean']
            ]
        }
    });
    // Remove old inline onsubmit to use syncQuill() in form HTML
    // document.getElementById('form-rapport').onsubmit = function() ...

    // Drag and Drop Logic
    const dropZone = document.getElementById('rapport-upload-zone');
    if (dropZone) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, e => {
                e.preventDefault();
                e.stopPropagation();
            }, false);
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => dropZone.classList.add('dragover'), false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => dropZone.classList.remove('dragover'), false);
        });

        dropZone.addEventListener('drop', e => {
            const dt = e.dataTransfer;
            const files = dt.files;
            const input = document.getElementById('rapport-image-input');
            input.files = files;
            previewImage(input);
        }, false);
    }
});

function openDeleteModal(actionName, idFieldName, idValue, message) {
    document.getElementById('delete-action').value = actionName;
    const idField = document.getElementById('delete-id-field');
    idField.name = idFieldName;
    idField.value = idValue;
    
    document.getElementById('delete-modal-msg').innerText = message;
    
    document.getElementById('modal-delete').classList.add('active');
}
