/**
 * Open a pop up window
 * 
 * @param string url 
 * @return void
 */
let popUpWin;
function open_app_gw( url ) {
  popUpWin = window.open( url, '', "directories=no,titlebar=no,toolbar=no,location=no,status=no,menubar=no,width=430,height=330" );

  window.addEventListener( 'message', ( event ) => {
    
    if( event.data === 'some-captions-close' ) {
      popUpWin.close();
    }
  }, false);
}

