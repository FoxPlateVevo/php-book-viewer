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
    
    $.fn.fullScreen = function(){
        return $.fullScreen.open(this);
    };
    
    $.fullScreen = {
        open: function(element){
            var nodeElement = element ? $(element).get(0) : $("body").get(0);
            
            if(nodeElement.requestFullscreen){
                nodeElement.requestFullscreen();
            }else if(nodeElement.msRequestFullscreen){
                nodeElement.msRequestFullscreen();
            }else if(nodeElement.mozRequestFullScreen){
                nodeElement.mozRequestFullScreen();
            }else if(nodeElement.webkitRequestFullScreen){
                nodeElement.webkitRequestFullScreen();
            }else{
                return false;
            }

            return true;
        },
        exit: function(){
            if(document.exitFullscreen){
                document.exitFullscreen();
            }else if(document.msExitFullscreen){
                document.msExitFullscreen();
            }else if(document.mozCancelFullScreen){
                document.mozCancelFullScreen();
            }else if(document.webkitExitFullscreen){
                document.webkitExitFullscreen();
            }else{
                return false;
            }

            return true;
        },
        handler: function(settings){
            var defaults = {
                onOpen  : function(){},
                onExit  : function(){}
            };

            var final = $.extend(true, {}, defaults, settings);

            $(document).on('webkitfullscreenchange mozfullscreenchange fullscreenchange MSFullscreenChange', function(e){
                if($.fullScreen.is()){
                    final.onOpen.call(this, e);
                }else{
                    final.onExit.call(this, e);
                }
            });
        },
        is: function(){
            return document.fullScreen || document.mozFullScreen || document.webkitIsFullScreen;
        }
    };
}(jQuery));