/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';

// start the Stimulus application
import 'bootstrap';
global.$ = global.jQuery = require('jquery');
import bsCustomFileInput from 'bs-custom-file-input';

bsCustomFileInput.init();


document.getElementById('searchBar').addEventListener('click', () => {
    let searchInput = document.getElementById('searchInput')
    if(searchInput.style.width == '25%'){
        searchInput.style.width = '0%'
        searchInput.style.border = 'none'
        searchInput.style.paddingLeft = '0'
    }else{
        searchInput.style.width = '25%'
        searchInput.style.border = '2px solid black'
        searchInput.style.paddingLeft = '10px'
    }
})


document.getElementById('sidebarBtn').addEventListener('click', () => {

    let sidebar = document.getElementById('sidebar')

    if(sidebar.style.width == '0%' || sidebar.style.width.length == 0){

        sidebar.style.width = '15%'
    }else{

        sidebar.style.width = '0%'
        sidebar.style.overflow = 'hidden'
    }
})

$('.showProductRating').on('click', function(e) {
    e.preventDefault();
    console.log('test')
    var productUrl = $(this).attr('href');

    $.get(productUrl, function(data) {
        $('#productModal .modal-body').html(data);
        $('#productModal').modal('show');
    });
});

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


$('#productModal').on('shown.bs.modal', function () {
    attachEventHandlers();
});

function attachEventHandlers() {

    function changeImage(element) {
        var imgSrc = element.getAttribute('data-img-src');
        var imgAlt = element.getAttribute('data-img-alt');
        var mainImage = document.getElementById('mainImage').getElementsByTagName('img')[0];
        mainImage.src = imgSrc;
        mainImage.alt = imgAlt;
    }

    const increaseButton = document.getElementById('increaseValue');
    const decreaseButton = document.getElementById('decreaseValue');

    if (increaseButton) {
        increaseButton.addEventListener('click', increaseValue);
    }

    if (decreaseButton) {
        decreaseButton.addEventListener('click', decreaseValue);
    }
}