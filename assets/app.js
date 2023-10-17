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

// start the Stimulus application
import './bootstrap';
import $ from 'jquery';
require('bootstrap');


document.getElementById('sidebarBtn').addEventListener('click', () => {
    console.log('clicked')
    let sidebar = document.getElementById('sidebar')
    let list = document.getElementById('nav')
    if(sidebar.style.width == '0%' || sidebar.style.width.length == 0){
        sidebar.style.width = '15%'
    }else{
        sidebar.style.width = '0%'
        sidebar.style.overflow = 'hidden'
    }
})