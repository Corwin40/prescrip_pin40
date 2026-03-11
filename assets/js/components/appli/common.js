import TomSelect from "tom-select";
import 'tom-select/dist/css/tom-select.css';
import tinymce from "tinymce";


export function useTomSelect(selector, option) {
    const TsSimple = {
        //plugins: ['remove_button'],
        create: true,
        onItemAdd:function(){
            this.setTextboxValue('');
            this.refreshOptions();
        },
        render:{
            option:function(data,escape){
                return '<div class="d-flex"><span>' + escape(data.data) + '</span><span class="ms-auto text-muted">' + escape(data.value) + '</span></div>';
            },
            item:function(data,escape){
                return '<div>' + escape(data.data) + '</div>';
            }
        }
    };
    const TsMulti = {
        plugins: ['remove_button'],
        create: true,
        onItemAdd:function(){
            this.setTextboxValue('');
            this.refreshOptions();
        },
        render:{
            option:function(data,escape){
                return '<div class="d-flex"><span>' + escape(data.data) + '</span><span class="ms-auto text-muted">' + escape(data.value) + '</span></div>';
            },
            item:function(data,escape){
                return '<div>' + escape(data.data) + '</div>';
            }
        }
    };
    console.log(option);

    if (option === 'Simple'){
        initializeTomSelect('.oneChoice', TsSimple);
    }
    else if (option === 'Multi'){
        initializeTomSelect('.multiChoice', TsMulti);
    }

    function initializeTomSelect(selector, options = {}) {
        document.querySelectorAll(selector).forEach(selectElement => {
            new TomSelect(selectElement, options);
        });
    }
}

export function handleNavLinkClick(event) {
    event.preventDefault();
    const clickedNavLink = event.target.closest('.nav-link');
    if (clickedNavLink) {
        clickedNavLink.classList.add('active');
        loadFormContent(clickedNavLink);}
}

export function removeOptions(selectElement) {
    for (let i = selectElement.options.length - 1; i >= 0; i -= 1) {
        selectElement.remove(i);
    }
}

export function zipcode(zipcodeInput, communeInput, select) {
    zipcodeInput.addEventListener('input', () => {
        if (zipcodeInput.value.length !== 5) return;

        axios
            .get(`https://apicarto.ign.fr/api/codes-postaux/communes/${zipcodeInput.value}`)
            .then(({ data }) => {
                removeOptions(select);

                data.forEach((el, idx) => {
                    const label = `${el.nomCommune.toUpperCase()} (${el.codePostal})`;
                    const opt = new Option(label, label, idx === 0, idx === 0);
                    select.options.add(opt);
                });

                if (data.length) {
                    zipcodeInput.value = data[0].codePostal;
                    communeInput.value = data[0].nomCommune.toUpperCase();
                }
            })
            .catch(() => alert('Pas de commune pour ce code postal'));
    });
}

export function change_selectcity(zipcode, commune, select){
    let regex = /^(.+) \((\d+)\)$/;
    let select_value = select.options[select.selectedIndex].text;
    const match = select_value.match(regex);
    zipcode.value = match[2];
    commune.value = match[1].toUpperCase();
}

export function formatDate(dateInput){
    dateInput.addEventListener('input', function (e) {
        let value = e.target.value.replace(/\D/g, ''); // supprime tout sauf chiffres
        if (value.length > 8) value = value.substring(0, 8); // max 8 chiffres
        let formatted = '';
        if (value.length > 0) {
            formatted += value.substring(0, 2);
        }
        if (value.length > 2) {
            formatted += '/' + value.substring(2, 4);
        }
        if (value.length > 4) {
            formatted += '/' + value.substring(4, 8);
        }
        // Appliquer le format
        e.target.value = formatted;
    });
    dateInput.addEventListener('paste', function (e) {
        e.preventDefault();
        const paste = (e.clipboardData || window.clipboardData).getData('text');
        const digits = paste.replace(/\D/g, '').substring(0, 8);
        let formatted = '';
        if (digits.length >= 2) formatted += digits.substring(0, 2);
        if (digits.length >= 4) formatted += '/' + digits.substring(2, 4);
        if (digits.length > 4) formatted += '/' + digits.substring(4, 8);
        e.target.value = formatted;
    });
}

// Affiche le nombre de caractère restant selon la limite
export function calculateChars(element, maxLength, charCountElement){
    const inputElement = element;
    const remainingChars = maxLength - inputElement.value.length;

    charCountElement.textContent = `${remainingChars} caractères restants`;

    inputElement.addEventListener('input', function() {
        const remainingChars = maxLength - this.value.length;
        charCountElement.textContent = `${remainingChars} caractères restants`;
        if (this.value.length > maxLength) {
            this.value = this.value.slice(0, maxLength);
            alert('Le texte est limité à $maxlength caractères.');
        }
    });
}

export function SelectChoice(listSelect, parentValue, url,ChoiceValue){
    if(listSelect.value >= 1){
        axios
            .get(url + parentValue)
            .then(
                function(response){
                    let SelectChoicevalues = response.data.values;
                    removeOptions(listSelect);
                    SelectChoicevalues.forEach((element)=>{
                        if (element.id === parseInt(ChoiceValue)){
                            let newOption = new Option (element.name, element.id, false, true);
                            listSelect.options.add(newOption);
                        }else{
                            let newOption = new Option (element.name, element.id);
                            listSelect.options.add(newOption);
                        }
                    });
                }
            )
            .catch(function(error){
                console.log(error);
            })
        ;
    }
}

export function selectChoiceOnChange(parentSelect, listSelect, url, ChoiceValue){
    let parentValue = parseInt(parentSelect.value);
    axios
        .get(url + parentValue)
        .then(
            function(response){
                let SelectChoicevalues = response.data.values;
                removeOptions(listSelect);
                SelectChoicevalues.forEach((element)=>{
                    if (element.id === parseInt(ChoiceValue)){
                        let newOption = new Option (element.name, element.id, false, true);
                        listSelect.options.add(newOption);
                    }else{
                        let newOption = new Option (element.name, element.id);
                        listSelect.options.add(newOption);
                    }
                });
            }
        )
        .catch(function(error){
            console.log(error);
        })
    ;
}

export function initializeTinyMCE(maxChars) {
    tinymce.remove(); // Supprime les instances existantes
    tinymce.init({
        selector: 'textarea.tinymce',
        skin: 'tinymce-5',
        setup: function(editor) {
            editor.on('input', function() {
                const content = editor.getContent({ format: 'text' });
                if (content.length > maxChars) {
                    const truncatedContent = content.substring(0, maxChars);
                    editor.setContent(truncatedContent);
                    alert(`La limite de ${maxChars} caractères a été atteinte.`);
                }
            });

            editor.on('keydown', function(event) {
                const content = editor.getContent({ format: 'text' });
                if (content.length >= maxChars && event.key !== "Backspace" && event.key !== "Delete") {
                    event.preventDefault();
                    alert(`La limite de ${maxChars} caractères a été atteinte.`);
                }
            });
        },
        plugins: 'image table lists visualchars wordcount',
        toolbar: 'undo redo | styles | bold italic alignleft aligncenter alignright alignjustify numlist bullist | link image',
        images_file_types: 'jpg,svg,webp',
        language: 'fr_FR',
        language_url: '/js/tinymce/js/tinymce/languages/fr_FR.js',
        entity_encoding: "raw",
        encoding: "html",
        paste_as_text: true,
        valid_elements: 'p,br,b,i,u,strong,em,ul,ol,li', // Exemple : limiter les balises autorisées
        valid_children: '+body[p,br,b,i,u,strong,em,ul,ol,li]', // Exemple : limiter les enfants autorisés
    });

    // mise en place du datapicker flatpickr sur les champs de date
    flatpickr(".flatpickr", {
        "locale": "fr",
        enableTime: false,
        allowInput: true,
        altFormat: "j F Y",
        dateFormat: "d/m/Y",
    });

// mise en place du datapicker flatpickr sur les champs de date
    flatpickr(".flatpickrtime", {
        "locale": "fr",
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true
    });
}