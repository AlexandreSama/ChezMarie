/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';

// start the Stimulus application
import $ from 'jquery';
import 'bootstrap';
import { Modal } from 'bootstrap';

document.querySelectorAll('.showProductRating').forEach(element => {
    element.addEventListener('click', function (e) {
        e.preventDefault();
        console.log(this)
        const productUrl = this.getAttribute('href');

        fetch(productUrl)
            .then(response => response.text())
            .then(data => {
                document.querySelector('#productModal .modal-body').innerHTML = data;
                const modalElement = document.getElementById('productModal');
                const productModal = new Modal(modalElement);
                productModal.show();
            });
    });
});

document.addEventListener('DOMContentLoaded', function () {
    // Fonctions pour augmenter et diminuer la quantité
    function increaseValue() {
        var maxQuantity = parseInt(document.getElementById('quantity').getAttribute('data-max'), 10);
        var value = parseInt(document.getElementById('quantity').value, 10);
        value = isNaN(value) ? 0 : value;
        if (value < maxQuantity) {
            value++;
            document.getElementById('quantity').value = value;
        }
    }

    function decreaseValue() {
        var value = parseInt(document.getElementById('quantity').value, 10);
        value = isNaN(value) ? 0 : value;
        value < 2 ? value = 1 : value--;
        document.getElementById('quantity').value = value;
    }

    // Fonction pour changer l'image principale
    function changeImage(element) {
        const imgSrc = element.getAttribute('data-img-src');
        const imgAlt = element.getAttribute('data-img-alt');
        const mainImage = document.getElementById('mainImage').querySelector('img');
        mainImage.src = imgSrc;
        mainImage.alt = imgAlt;
    }

    // Fonction pour attacher des gestionnaires d'événements aux boutons et vignettes
    function attachEventHandlers() {
        const increaseButton = document.getElementById('increaseValue');
        const decreaseButton = document.getElementById('decreaseValue');

        if (increaseButton) {
            increaseButton.addEventListener('click', increaseValue);
        }

        if (decreaseButton) {
            decreaseButton.addEventListener('click', decreaseValue);
        }

        document.querySelectorAll('.thumbnail').forEach(thumbnail => {
            thumbnail.addEventListener('click', function () {
                changeImage(this);
            });
        });
    }

    // Écouteur d'événement pour la modal, une fois affichée
    const productModalElement = document.getElementById('productModal');
    if (productModalElement) {
        productModalElement.addEventListener('shown.bs.modal', function () {
            attachEventHandlers();
        });
    }

    const datetimeField = document.getElementById('desiredPickupDate');

    if (datetimeField) {
        datetimeField.addEventListener('change', function () {
            const selectedDate = new Date(this.value);
            const hour = selectedDate.getHours();

            if (hour < 8) {
                selectedDate.setHours(8); // Si l'heure est avant 8h, la fixer à 8h
                selectedDate.setMinutes(0); // Réinitialiser les minutes
            } else if (hour > 18) {
                selectedDate.setHours(18); // Si l'heure est après 18h, la fixer à 18h
                selectedDate.setMinutes(0); // Réinitialiser les minutes
            }

            // Mettre à jour le champ de formulaire avec la nouvelle valeur
            this.value = selectedDate.toISOString().substring(0, 16);
        });

    }

    if (document.querySelector('.rating')) {
        const productId = document.querySelector('.rating').dataset.product;

        document.querySelectorAll('.star').forEach(star => {
            star.addEventListener('click', (event) => {
                event.preventDefault();

                const ratingValue = event.target.dataset.value;
                const form = document.getElementById('rating-form-' + productId);
                const ratingInput = document.querySelector('.rating-' + productId);

                ratingInput.value = ratingValue;
                form.submit();
            });
        });
    }


    if(document.querySelector('#lockoutTimer')){
        var lockoutTimerDiv = document.getElementById("lockoutTimer");
        var lockoutTimeRemaining = lockoutTimerDiv.dataset.lockoutTimeRemaining
    
    
        if(lockoutTimeRemaining !== 0){
            var x = setInterval(function() {
    
                lockoutTimerDiv.textContent = lockoutTimeRemaining + " secondes restantes avant de pouvoir réessayer";
                
                lockoutTimeRemaining = lockoutTimeRemaining - 1
                if (lockoutTimeRemaining < 0) {
                    clearInterval(x);
                    lockoutTimerDiv.textContent = "Vous pouvez maintenant essayer de vous connecter à nouveau.";
                    document.querySelector("button[type='submit']").disabled = false;
                }
            }, 1000);
        }
    }

    const countryCodeSelector = document.querySelector('#countryCode');
    const phoneNumberInput = document.querySelector('#phoneNumber');

    function updateFullPhoneNumber() {
        // Combine countryCode et phoneNumber
        phoneNumberInput.value = countryCodeSelector.value + phoneNumberInput.value;
    }

    // Écoute les changements sur les champs countryCode et phoneNumber
    countryCodeSelector.addEventListener('change', updateFullPhoneNumber);

    // Met à jour le numéro de téléphone complet initial
    updateFullPhoneNumber();
});