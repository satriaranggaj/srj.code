const menuToggle = document.querySelector('.menu-toggle input');
const nav = document.querySelector('nav#navbar .nav-link');
const bucon = document.querySelector('.button-contact');

menuToggle.addEventListener('click', function() {
    nav.classList.toggle('slide');
    bucon.classList.toggle('bucon-mobile');
});

window.onscroll = function(){
    document.getElementById('navbar').style.boxShadow = '0px 3px 20px -10px black';

    const navbarTop = document.getElementById('navbar');
    window.addEventListener('scroll', function() {
        if (window.pageYOffset === 0) {
          navbarTop.style.boxShadow = 'none'; // Mengubah properti CSS saat scroll ke atas
        }
      });
};

const scriptURL = 'https://script.google.com/macros/s/AKfycbyuqtPPjFO-zXGSp1JRUhtDHuC43HdpaAVgNjsAAPHWGFOjgjiAqk5U4IEnjf9Q37QL/exec';
const form = document.forms['submit-to-google-sheet']
const btnSubmit = document.querySelector('.btn-submit');
const btnLoading = document.querySelector('.btn-loading');
const alert = document.querySelector('.alert');

form.addEventListener('submit', e => {
  e.preventDefault();
  // ketika tombol submit di klik
  // tampilkan tombol loading, hilangkan tombol submit
  btnLoading.classList.toggle('d-none');
  btnSubmit.classList.toggle('d-none');
  fetch(scriptURL, { method: 'POST', body: new FormData(form)})
    .then(response => {
      // tampilkan tombol submit, hilangkan tombol loading
      btnLoading.classList.toggle('d-none');
      btnSubmit.classList.toggle('d-none');
      // tampilkan alert
      alert.classList.toggle('d-none');
      // reset form
      form.reset();
      console.log('Success!', response)
    })
    .catch(error => console.error('Error!', error.message))
  });
