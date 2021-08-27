window.onload = function() {
    //Get the button
    var mybutton = document.getElementById("go-to-top");

    // When the user scrolls down 20px from the top of the document, show the button
    window.onscroll = function() {scrollFunction()};

    function scrollFunction() {
      if (document.body.scrollTop > 250 || document.documentElement.scrollTop > 250) {
        // mybutton.style.display = "block";
        mybutton.style.opacity = 100;
      } else {
        // mybutton.style.display = "none";
        mybutton.style.opacity = 0;
      }
    }

    // When the user clicks on the button, scroll to the top of the document
    function topFunction() {
      document.body.scrollTop = 0;
      document.documentElement.scrollTop = 0;
    }
}
