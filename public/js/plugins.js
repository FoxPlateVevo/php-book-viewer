/*
 * Extensions
 * Version 0.1
 */
(function($){
    /*
     * action : optional String [auto]
     */
    $.fn.MyFunction = function(action) {
        $(this).each(function(){
            console.log(this);
        });
        
        return this;
    };
    
    /*
     * settings : Object
     */
    $.fn.mousewheelOrientation = function(settings) {
        var defaults = {
            up: function(){},
            left: function(){},
            right: function(){},
            down: function(){}
        };

        var final = $.extend(true, {}, defaults, settings);

        $(this).mousewheel(function(e){
            if(e.deltaY < 0){ //down
                final.down.call(this, e);
            }else if(e.deltaY > 0){ //up
                final.up.call(this, e);
            }else if(e.deltaX < 0){ //left
                final.left.call(this, e);
            }else if(e.deltaX > 0){ //right
                final.right.call(this, e);
            }
        });

        return this;
    };
}(jQuery));