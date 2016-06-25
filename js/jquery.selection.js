/*!
 * jQuery.selection - jQuery Plugin
 *
 * Copyright (c) 2010-2014 IWASAKI Koji (@madapaja).
 * http://blog.madapaja.net/
 * Under The MIT License
 *
 * Adaption to WordPress coding standards
 * Copyright (c) 2016 Peter Putzer
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
( function( $, win, doc ) {
    /**
     * Get caret status of the selection of the element
     *
     * @param   {Element}   element         target DOM element
     * @return  {Object}    return
     * @return  {String}    return.text     selected text
     * @return  {Number}    return.start    start position of the selection
     * @return  {Number}    return.end      end position of the selection
     */
    var _getCaretInfo = function( element ) {
        var res = {
            text: '',
            start: 0,
            end: 0
        }, range, range2;

        if ( ! element.value ) {

            // No value or empty string
            return res;
        }

        try {
            if ( win.getSelection ) {

                // Except IE
                res.start = element.selectionStart;
                res.end = element.selectionEnd;
                res.text = element.value.slice( res.start, res.end );
            } else if ( doc.selection ) {

                // For IE
                element.focus();

                range = doc.selection.createRange();
                range2 = doc.body.createTextRange();

                res.text = range.text;

                try {
                    range2.moveToElementText( element );
                    range2.setEndPoint( 'StartToStart', range );
                } catch ( e ) {
                    range2 = element.createTextRange();
                    range2.setEndPoint( 'StartToStart', range );
                }

                res.start = element.value.length - range2.text.length;
                res.end = res.start + range.text.length;
            }
        } catch ( e ) {

            // Give up
        }

        return res;
    };

    /**
     * Caret operation for the element
     * @type {Object}
     */
    var _CaretOperation = {
        /**
         * Get caret position
         *
         * @param   {Element}   element         target element
         * @return  {Object}    return
         * @return  {Number}    return.start    start position for the selection
         * @return  {Number}    return.end      end position for the selection
         */
        getPos: function( element ) {
            var tmp = _getCaretInfo( element );
            return { start: tmp.start, end: tmp.end };
        },

        /**
         * Set caret position
         *
         * @param   {Element}   element         target element
         * @param   {Object}    toRange         caret position
         * @param   {Number}    toRange.start   start position for the selection
         * @param   {Number}    toRange.end     end position for the selection
         * @param   {String}    caret           caret mode: any of the following: "keep" | "start" | "end"
         */
        setPos: function( element, toRange, caret ) {
            var range;

            caret = this._caretMode( caret );

            if ( 'start' === caret ) {
                toRange.end = toRange.start;
            } else if ( 'end' === caret ) {
                toRange.start = toRange.end;
            }

            element.focus();
            try {
                if ( element.createTextRange ) {
                    range = element.createTextRange();

                    if ( win.navigator.userAgent.toLowerCase().indexOf( 'msie' ) >= 0 ) {
                        toRange.start = element.value.substr( 0, toRange.start ).replace( /\r/g, '' ).length;
                        toRange.end = element.value.substr( 0, toRange.end ).replace( /\r/g, '' ).length;
                    }

                    range.collapse( true );
                    range.moveStart( 'character', toRange.start );
                    range.moveEnd( 'character', toRange.end - toRange.start );

                    range.select();
                } else if ( element.setSelectionRange ) {
                    element.setSelectionRange( toRange.start, toRange.end );
                }
            } catch ( e ) {

                // Give up
            }
        },

        /**
         * Get selected text
         *
         * @param   {Element}   element         target element
         * @return  {String}    return          selected text
         */
        getText: function( element ) {
            return _getCaretInfo( element ).text;
        },

        /**
         * Get caret mode
         *
         * @param   {String}    caret           caret mode
         * @return  {String}    return          any of the following: "keep" | "start" | "end"
         */
        _caretMode: function( caret ) {
            caret = caret || 'keep';
            if ( false === caret ) {
                caret = 'end';
            }

            switch ( caret ) {
                case 'keep':
                case 'start':
                case 'end':
                    break;

                default:
                    caret = 'keep';
            }

            return caret;
        },

        /**
         * Replace selected text
         *
         * @param   {Element}   element         target element
         * @param   {String}    text            replacement text
         * @param   {String}    caret           caret mode: any of the following: "keep" | "start" | "end"
         */
        replace: function( element, text, caret ) {
            var tmp = _getCaretInfo( element ),
                orig = element.value,
                pos = $( element ).scrollTop(),
                range = { start: tmp.start, end: tmp.start + text.length };

            element.value = orig.substr( 0, tmp.start ) + text + orig.substr( tmp.end );

            $( element ).scrollTop( pos );
            this.setPos( element, range, caret );
        },

        /**
         * Insert before the selected text
         *
         * @param   {Element}   element         target element
         * @param   {String}    text            insertion text
         * @param   {String}    caret           caret mode: any of the following: "keep" | "start" | "end"
         */
        insertBefore: function( element, text, caret ) {
            var tmp = _getCaretInfo( element ),
                orig = element.value,
                pos = $( element ).scrollTop(),
                range = { start: tmp.start + text.length, end: tmp.end + text.length };

            element.value = orig.substr( 0, tmp.start ) + text + orig.substr( tmp.start );

            $( element ).scrollTop( pos );
            this.setPos( element, range, caret );
        },

        /**
         * Insert after the selected text
         *
         * @param   {Element}   element         target element
         * @param   {String}    text            insertion text
         * @param   {String}    caret           caret mode: any of the following: "keep" | "start" | "end"
         */
        insertAfter: function( element, text, caret ) {
            var tmp = _getCaretInfo( element ),
                orig = element.value,
                pos = $( element ).scrollTop(),
                range = { start: tmp.start, end: tmp.end };

            element.value = orig.substr( 0, tmp.end ) + text + orig.substr( tmp.end );

            $( element ).scrollTop( pos );
            this.setPos( element, range, caret );
        }
    };

    /* Add jQuery.selection */
    $.extend( {
        /**
         * Get selected text on the window
         *
         * @param   {String}    mode            selection mode: any of the following: "text" | "html"
         * @return  {String}    return
         */
        selection: function( mode ) {
            var getText = ( 'text' === ( mode || 'text' ).toLowerCase() );
            var sel;

            try {
                if ( win.getSelection ) {
                    if ( getText ) {

                        // Get text
                        return win.getSelection().toString();
                    } else {

                        // Get html
                        sel = win.getSelection(), range;

                        if ( sel.getRangeAt ) {
                            range = sel.getRangeAt( 0 );
                        } else {
                            range = doc.createRange();
                            range.setStart( sel.anchorNode, sel.anchorOffset );
                            range.setEnd( sel.focusNode, sel.focusOffset );
                        }

                        return $( '<div></div>' ).append( range.cloneContents() ).html();
                    }
                } else if ( doc.selection ) {
                    if ( getText ) {

                        // Get text
                        return doc.selection.createRange().text;
                    } else {

                        // Get HTML
                        return doc.selection.createRange().htmlText;
                    }
                }
            } catch ( e ) {
                /* Give up */
            }

            return '';
        }
    } );

    // Add selection
    $.fn.extend( {
        selection: function( mode, opts ) {
            opts = opts || {};

            switch ( mode ) {
                /**
                 * Get caret position:
                 * selection('getPos')
                 *
                 * @return  {Object}    return
                 * @return  {Number}    return.start    start position for the selection
                 * @return  {Number}    return.end      end position for the selection
                 */
                case 'getPos':
                    return _CaretOperation.getPos( this[0] );

                /**
                 * Set caret position:
                 * selection('setPos', opts)
                 *
                 * @param   {Number}    opts.start      start position for the selection
                 * @param   {Number}    opts.end        end position for the selection
                 */
                case 'setPos':
                    return this.each( function() {
                        _CaretOperation.setPos( this, opts );
                    } );

                /**
                 * Replace the selected text:
                 * selection('replace', opts)
                 *
                 * @param   {String}    opts.text            replacement text
                 * @param   {String}    opts.caret           caret mode: any of the following: "keep" | "start" | "end"
                 */
                case 'replace':
                    return this.each( function() {
                        _CaretOperation.replace( this, opts.text, opts.caret );
                    } );

                /**
                 * Insert before/after the selected text:
                 * selection('insert', opts)
                 *
                 * @param   {String}    opts.text            insertion text
                 * @param   {String}    opts.caret           caret mode: any of the following: "keep" | "start" | "end"
                 * @param   {String}    opts.mode            insertion mode: any of the following: "before" | "after"
                 */
                case 'insert':
                    return this.each( function() {
                        if ( 'before' === opts.mode ) {
                            _CaretOperation.insertBefore( this, opts.text, opts.caret );
                        } else {
                            _CaretOperation.insertAfter( this, opts.text, opts.caret );
                        }
                    } );

                /**
                 * Get selected text:
                 * selection('get')
                 *
                 * @return  {String}    return
                 */
                case 'get':

                    // Falls through
                default:
                    return _CaretOperation.getText( this[0] );
            }

            return this;
        }
    } );
} )( jQuery, window, window.document );
