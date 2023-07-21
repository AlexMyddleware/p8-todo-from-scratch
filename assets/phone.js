console.log('phone.js loaded');
var hiddenOnMobile = document.getElementsByClassName('hidden-on-mobile');

document.addEventListener('DOMContentLoaded', function() {
  var buttonShowMedia = document.getElementById('button-show-media');

  if (buttonShowMedia) {
    buttonShowMedia.addEventListener('click', function() {
      var mediaContainer = document.getElementsByClassName('media-container')[0];
      if (mediaContainer.classList.contains('hidden')) {
        mediaContainer.classList.remove('hidden');
        buttonShowMedia.innerHTML = 'Hide Media';
      } else {
        mediaContainer.classList.add('hidden');
        buttonShowMedia.innerHTML = 'Show Media';
      }
    });
  } else {
    console.log("button-show-media element not found");
  }
});


// Function to get the current screen width
function getScreenWidth() {
    return window.innerWidth;
  }
  
  // Function to handle screen width changes
  function handleScreenWidthChange() {
    var screenWidth = getScreenWidth();
    if (screenWidth < 768) {
      for (var i = 0; i < hiddenOnMobile.length; i++) {
        hiddenOnMobile[i].setAttribute('disabled', 'true');
        hiddenOnMobile[i].style.display = 'none';
      }
    } else {
    for (var i = 0; i < hiddenOnMobile.length; i++) {
        hiddenOnMobile[i].setAttribute('disabled', 'false');
        hiddenOnMobile[i].style.display = 'flex';
        
      }
    
    }
  
  }
  
  // Get the initial screen width
  var screenWidth = getScreenWidth();
  handleScreenWidthChange();
  
  // Add an event listener for the window resize event
  window.addEventListener('resize', handleScreenWidthChange);
  
