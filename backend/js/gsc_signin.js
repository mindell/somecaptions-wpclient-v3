/**
 * Open a pop up window
 * 
 * @param string url 
 * @return void
 */
let popUpWin;
var somecaptions_location = window.location.href + '&gsc_connected=1';
function open_app_gw( url ) {
  popUpWin = window.open( url, '', "directories=no,titlebar=no,toolbar=no,location=no,status=no,menubar=no,width=430,height=330" );

  window.addEventListener( 'message', ( event ) => {
    if( event.data === 'some-captions-close' ) {
      popUpWin.close();
      window.location.href = somecaptions_location;
    }
  }, false);
}

