let menuButton = document.getElementById("menuButton");
let listMenu = document.getElementById("listMenu");
menuButton.addEventListener("click", function(e){
    listMenu.classList.toggle("open");
})