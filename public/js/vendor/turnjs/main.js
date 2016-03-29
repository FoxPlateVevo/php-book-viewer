function initApp() {
    var $magazine = $(".magazine");
    var $thumbnails = $(".thumbnails");
    var $magazineViewport = $(".magazine-viewport");
    
    // Check if the CSS was already loaded
    if ($magazine.width() === 0 || $magazine.height() === 0) {
        setTimeout(initApp, 10);
        return;
    }
    
    // Create the flipbook
    $magazine.turn({
        //display mode
        display: "double", //default: double [double, single]
        // Magazine width
        width: 900,
        // Magazine height
        height: 600,
        // Duration in millisecond
        duration: 1000,
        // Hardware acceleration
        acceleration: !isChrome(),
        // Enables gradients
        gradients: true,
        // Auto center this flipbook
        autoCenter: true,
        // Elevation from the edge of the flipbook when turning a page
        elevation: 50,
        // The number of pages
        pages: window.book.pages,
        // Events
        when: {
            turning: function (event, page, view) {
                console.log("turning...");
                
                window.Hash.go("page/" + page).update();
                selectThumbnail(page);
            },
            turned: function (event, page, view) {
                console.log("turned...");
                
                disableControls(page);
                
                $(this).turn("center");
                
                if (page === 1) {
                    selectThumbnail(page);
                    $(this).turn("peel", "tr");
                }
            },
            missing: function (event, pages) {
//                console.log("missing...");
//                console.log(pages);
                
                // Add pages that aren't in the magazine
                $.each(pages, function(index, page){
                    addPage(page, $magazine);
                });
            }
        }
    });

    // Zoom.js
    $magazineViewport.zoom({
        flipbook: $magazine,
        max: function () {
            return largeMagazineWidth() / $magazine.width();
        },
        when: {
            swipeLeft: function () {
                $(this).zoom("flipbook").turn("next");
            },
            swipeRight: function () {
                $(this).zoom("flipbook").turn("previous");
            },
            resize: function (event, scale, page, pageElement) {
                if(scale === 1){
                    loadSmallPage(page, pageElement);
                }else{
                    loadLargePage(page, pageElement);
                }
            },
            zoomIn: function(){
                $magazine.removeClass("animated").addClass("zoom-in");
                
                $(".toolbar .icon.zoom").html("<i class='icon-arrows-compress'></i>");
            },
            zoomOut: function () {
                $magazine.addClass("animated").removeClass("zoom-in");
                $(".toolbar .icon.zoom").html("<i class='icon-arrows-expand'></i>");
                
                resizeViewport();
            }
        }
    });

    /*
     * Viewport zoom events
     */
    if ($.isTouch){
        $magazineViewport.bind("zoom.doubleTap", window.zoomTo);
    }else{
        $magazineViewport.bind("zoom.tap", window.zoomTo);
    }
    
    // Using arrow keys to turn the page
    $(document).keydown(function (e) {
        var previous = 37,
        next = 39,
        esc = 27;
        
        switch (e.keyCode) {
            case previous:
                // left arrow
                $magazine.turn("previous");
                e.preventDefault();

                break;
            case next:
                //right arrow
                $magazine.turn("next");
                e.preventDefault();
                
                break;
            case esc:
                $magazineViewport.zoom('zoomOut');
                e.preventDefault();
                
                break;
        }
    });

    // URIs - Format #/page/1 
    window.Hash.on("^page\/([0-9]*)$", {
        yep: function (path, parts) {
            var page = parts.pop();
            
            if (page !== undefined) {
                if ($magazine.turn("is")){
                    $magazine.turn("page", page);
                }
            }
        },
        nop: function (path) {
            if ($magazine.turn("is")){
                $magazine.turn("page", 1);
            }
        }
    });


    $(window).resize(function () {
        resizeViewport();
    }).bind('orientationchange', function () {
        resizeViewport();
    });

    // Events for thumbnails
    $thumbnails.find("li")
    .click(function(){
        var page = $(this).attr("data-page");
        
        $magazine.turn("page", page);
        window.Hash.go("page/" + page).update();
        selectThumbnail(page);
    })
    .bind($.mouseEvents.over, function () {
        $(this).addClass("thumb-hover");
    })
    .bind($.mouseEvents.out, function () {
        $(this).removeClass("thumb-hover");
    });

    /*
     * Setting user agent with touch 
     */
    if($.isTouch){
        $thumbnails
        .addClass('thumbanils-touch')
        .bind($.mouseEvents.move, function(e){
            e.preventDefault();
        });
    }else{
        $thumbnails.find("ul")
        .mouseover(function(){
            $thumbnails.addClass('thumbnails-hover');
        })
        .mousedown(function () {
            return false;
        })
        .mouseout(function () {
            $thumbnails.removeClass('thumbnails-hover');
        });
    }
    
    // Regions
    if ($.isTouch) {
        $magazine.bind('touchstart', window.regionClick);
    } else {
        $magazine.click(window.regionClick);
    }
    
    /*
     * Magazinet buttons
     */
    // Events for the next button
    $(".next-button")
    .bind($.mouseEvents.over, function () {
        $(this).addClass('next-button-hover');
    })
    .bind($.mouseEvents.out, function () {
        $(this).removeClass('next-button-hover');
    })
    .bind($.mouseEvents.down, function () {
        $(this).addClass('next-button-down');
    })
    .bind($.mouseEvents.up, function () {
        $(this).removeClass('next-button-down');
    })
    .click(function () {
        $magazine.turn('next');
    });

    // Events for the previus button
    $(".previous-button")
    .bind($.mouseEvents.over, function () {
        $(this).addClass('previous-button-hover');
    })
    .bind($.mouseEvents.out, function () {
        $(this).removeClass('previous-button-hover');
    })
    .bind($.mouseEvents.down, function () {
        $(this).addClass('previous-button-down');
    })
    .bind($.mouseEvents.up, function () {
        $(this).removeClass('previous-button-down');
    })
    .click(function () {
        $magazine.turn('previous');
    });

    resizeViewport();
    
    $magazine.addClass('animated');
}

$(document).ready(function(){
    /*
     * Thumbnails
     */
    var animationProccessing;
    var $magazine = $(".magazine");
    var $thumbnails = $(".thumbnails");
    var $magazineViewport = $(".magazine-viewport");
    var thumbnailWidth = $thumbnails.find("li").outerWidth(true) + 5;
    
    $thumbnails.find("ul")
    .css({
        "width": (thumbnailWidth * $thumbnails.find("li").length) + "px"
    })
    .mousewheel(function(e){
        e.preventDefault();

        if(animationProccessing){
            return;
        }
        
        var currentLeft = parseInt($(this).css("margin-left"), 10);
        var thumbnailsWidth = $thumbnails.width();
        var width = $(this).width();
        
        if((e.deltaY < 0 || e.deltaX < 0) && (width - Math.abs(currentLeft)) > thumbnailsWidth){ //left
            animationProccessing = true;
            
            $(this).animate({"margin-left": currentLeft - thumbnailWidth}, 100, function(){
                animationProccessing = false;
            });
        }else if((e.deltaY > 0 || e.deltaX > 0) && currentLeft < 0){ //rigth
            animationProccessing = true;
            
            $(this).animate({"margin-left": currentLeft + thumbnailWidth}, 100, function(){
                animationProccessing = false;
            });
        }
    });
    
    /*
     * Navbar 
     */
    $(".navbar-container .icon.first").click(function(){
        $magazine.turn("page", 1);
    });
    
    $(".navbar-container .icon.previous").click(function(){
        $magazine.turn("previous");
    });
    
    $(".navbar-container .icon.next").click(function(){
        $magazine.turn("next");
    });
    
    $(".navbar-container .icon.last").click(function(){
        var totalPages = $magazine.turn("pages");
        
        $magazine.turn("page", totalPages);
    });
    
    $(".navbar-container input[data-current-page]").on("input", function(){
        var page = $(this).val();
        
        if(page && page > 0 && page <= window.book.pages){
            $magazine.turn("page", page);
        }else if(page){
            $.message("La página " + page + ", no existe en este libro");
        }
    });
    
    /*
     * Navigation 
     */
    //toolbar
    //zoom
    $(".toolbar .icon.zoom")
    .bind('click', function () {
        if($(this).find("i.icon-arrows-expand").length){
            $magazineViewport.zoom("zoomIn");
        }else if($(this).find("i.icon-arrows-compress").length){
            $magazineViewport.zoom("zoomOut");
        }
    });
    
    //slider
    $(".toolbar .icon.slider").click(function(){
        if ($thumbnails.is(":visible")) {
            $thumbnails.slideUp();
        } else {
            $thumbnails.slideDown();
            
            /*
             * Set thumbnail position
             */
            var thumbnailWidth = $thumbnails.find("li").outerWidth(true) + 5;
            
            $thumbnails.find("ul").css("marginLeft", -($magazine.turn("page") - 1) * thumbnailWidth);
        }
    });
    
    $(document).click(function(e){
        /*
         * click event handler in the document for thumbnails containers
         */
        if(
            !$thumbnails.find($(e.target)).length && 
            !$(e.target).is(".toolbar .icon.slider") &&
            !$(e.target).is(".toolbar .icon.slider i")
        ){
            $thumbnails.slideUp();
        }
    });
    
    $thumbnails.css({
        visibility: "initial"
    })
    .hide();
});