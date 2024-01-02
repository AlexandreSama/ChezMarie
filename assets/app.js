/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/bootstrap.css';
import './styles/navbar.css';
import './styles/sidebar.css';
import './styles/main.css';
import './styles/home.css';
import './styles/products.css';
import './styles/spé_product.css';
import './styles/subbar.css'

// start the Stimulus application
import './bootstrap';
global.$ = global.jQuery = require('jquery');
require('bootstrap');


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