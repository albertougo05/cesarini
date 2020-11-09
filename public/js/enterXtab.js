// Map [Enter] key to work like the [Tab] key
// Daniel P. Clark 2014
// https://snipplr.com/view/145654/enter-key-press-behaves-like-a-tab-in-javascript/
 
// Catch the keydown for the entire document
$(document).keydown(function(e) {
 
  // Set self as the current item in focus
  var self = $(':focus'),
      // Set the form by the current item in focus
      form = self.parents('form:eq(0)'),
      focusable;
 
  // Array of Indexable/Tab-able items
  focusable = form.find('input,a,select,button,textarea,div[contenteditable=true]').filter(':visible');
 
  function enterKey(){
    if (e.which === 13 && !self.is('textarea,div[contenteditable=true]')) { // [Enter] key
 
      // If not a regular hyperlink/button/textarea
      if ($.inArray(self, focusable) && (!self.is('a,button'))){
        // Then prevent the default [Enter] key behaviour from submitting the form
        e.preventDefault();
      } // Otherwise follow the link/button as by design, or put new line in textarea
 
      // Focus on the next item (either previous or next depending on shift)
      focusable.eq(focusable.index(self) + (e.shiftKey ? -1 : 1)).focus();
 
      return false;
    }
  }
  // We need to capture the [Shift] key and check the [Enter] key either way.
  if (e.shiftKey) { enterKey() } else { enterKey() }
});


//
// Funciones Utiles Comunes a todos
// 
var COMMONS = {

  // Devuelve verdadero si el string está vacio
  isEmpty: function(str) {    // Verifica string vacios

    return (str.length === 0 || !str.trim());
  },

  // Convierte string '1.250,25' A (float) 1250.25
  strToFloat: function(num) {
    num = num.replace('.', '');
    num = num.replace(',', '.');
    num = parseFloat(num);

    return num.toFixed(2);
  },



};