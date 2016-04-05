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
    
    $.fn.send = function(callback, dataType) {
        this.each(function(){
            var form = this,
            $form = $(form);
            
            if($form.is("form") && $form.attr("action")){
                $form.submit(submitHandler);
            }
        });
        
        function submitHandler(e) {
            e.preventDefault();
            
            var form        = this,
            action          = $(form).attr("action"),
            formElements    = form.elements,
            params          = {};

            $.each(formElements, function(i, element){
                if(element.name){
                    var value, success = true;
                    
                    switch (element.type) {
                        case "button":
                        case "file":
                        case "hidden":
                        case "number":
                        case "password":
                        case "select-one":
                        case "submit":
                        case "text":
                        case "textarea":
                            value = element.value;
                            break;
                        case "checkbox":
                        case "radio":
                            if (element.checked) {
                                value = element.value;
                            } else {
                                success = false;
                            }
                            break;
                    }

                    if(success){
                        var elementName = element.name;
                        value = value.replace(/'/g, '"').replace(/&/g, " ");

                        switch (element.type){
                            case "checkbox":
                                if(elementName.match(/\[\]/)){
                                    elementName = elementName.replace(/\[\]/, "");

                                    if(!params[elementName]){
                                        params[elementName] = [];
                                    }

                                    params[elementName].push(value);
                                }else{
                                    params[elementName] = value;
                                }
                                break;
                            default:
                                params[elementName] = value;
                                break;
                        }
                    }
                }
            });

            var disabledElements = function(disabled){
                $(formElements).each(function(){
                    switch (this.type) {
                            case "button":
                            case "checkbox":
                            case "file":
                            case "select-one":
                            case "radio":
                            case "submit":
                            case "textarea":
                                $(this).attr("disabled", disabled);
                                break;
                            case "hidden":
                            case "number":
                            case "password":
                            case "text":
                                $(this).attr("readonly", disabled);
                                break;
                        }
                });
            };

            console.log(params);

            //prepare elements to wait the request
            disabledElements(true);
            
            $.post(action, params, function(response){
                disabledElements(false);
                
                typeof callback === "function" && callback(response);
            }, dataType)
            .fail(function(xhr, errorType, errorMessage){
                console.log(errorMessage);
                console.log(xhr.responseText);

                switch(errorType){
                    case "parsererror":
                        console.log("Error in parser, type " + dataType);
                        break;
                }
            });
        }
        
        return this;
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