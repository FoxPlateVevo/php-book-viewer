/*
 * Magazine core functions
 */
function addPage(pageNumber, book) {
    // Create a new element for this page
    var element = $("<div>");
    
    // Add the page to the flipbook
    if (book.turn("addPage", element, pageNumber)) {
        // Add the initial HTML
        // It will contain a loader indicator and a gradient
        element.html('<div class="gradient"></div><div class="loader"><div class="loader-content"><div class="loader-icon"></div></div></div>');
        
        // Load the page
        loadPage(pageNumber, element);
    }
}

function loadPage(page, pageElement) {
    var $thumbnails = $(".thumbnails");
    
    // Create an image element
    var $img = $("<img>");
    
    $img
    .mousedown(function (e) {
        e.preventDefault();
    })
    .attr("src", window.book.path + "/pages/small-page-" + page + ".png")
    .load(function () {
        // Set the size
        $(this).css({
            width: "100%",
            height: "100%"
        })
        .appendTo(pageElement);
        
        // Remove the loader indicator
        pageElement.find(".loader").remove();
        
        // Add background image to thumbnail page
        $thumbnails.find("li[data-page=" + page + "] img")
        .attr("src", window.book.path + "/pages/small-page-" + page + ".png")
        .parent()
        .css("backgroundImage", "none");
    });
    
    loadRegions(page, pageElement);
}

// Zoom in / Zoom out
function zoomTo(event) {
    var $magazineViewPort = $(".magazine-viewport");
    
    if ($magazineViewPort.data().regionClicked) {
        $magazineViewPort.data().regionClicked = false;
    } else {
        if ($magazineViewPort.zoom("value") === 1) {
            $magazineViewPort.zoom("zoomIn", event);
        } else {
            $magazineViewPort.zoom("zoomOut");
        }
    }
}

// Load regions
function loadRegions(page, element) {
//    $.getJSON(window.book.path + "/pages/regions-page-" + page + '.json')
//    .done(function (data) {
//        $.each(data, function (key, region) {
//            addRegion(region, element);
//        });
//    });
}

// Add region
function addRegion(region, pageElement) {
    var reg = $('<div />', {'class': 'region  ' + region['class']}),
            options = $('.magazine').turn('options'),
            pageWidth = options.width / 2,
            pageHeight = options.height;
    
    reg.css({
        top: Math.round(region.y / pageHeight * 100) + '%',
        left: Math.round(region.x / pageWidth * 100) + '%',
        width: Math.round(region.width / pageWidth * 100) + '%',
        height: Math.round(region.height / pageHeight * 100) + '%'
    }).attr('region-data', $.param(region.data || ''));
    
    reg.appendTo(pageElement);
}

// Process click on a region
function regionClick(event) {
    var region = $(event.target);

    if (region.hasClass('region')) {
        $('.magazine-viewport').data().regionClicked = true;
        
        setTimeout(function () {
            $('.magazine-viewport').data().regionClicked = false;
        }, 100);

        var regionType = $.trim(region.attr('class').replace('region', ''));
        
        return processRegion(region, regionType);
    }
}

// Process the data of every region

function processRegion(region, regionType) {
    data = decodeParams(region.attr('region-data'));
    
    switch (regionType) {
        case 'link' :
            window.open(data.url);
            break;
        case 'zoom' :
            var regionOffset = region.offset(),
            viewportOffset = $('.magazine-viewport').offset(),
            pos = {
                x: regionOffset.left - viewportOffset.left,
                y: regionOffset.top - viewportOffset.top
            };
            
            $('.magazine-viewport').zoom('zoomIn', pos);
            break;
        case 'to-page' :
            $('.magazine').turn('page', data.page);
            break;
    }

}

// Load large page
function loadLargePage(page, pageElement) {
    var img = $('<img />');

    img.load(function () {
        var prevImg = pageElement.find('img');
        $(this).css({width: '100%', height: '100%'});
        $(this).appendTo(pageElement);
        prevImg.remove();
    });

    // Loadnew page
    img.attr('src',  window.book.path + "/pages/large-page-" + page + ".png");
}

// Load small page
function loadSmallPage(page, pageElement) {
    var img = pageElement.find('img');

    img.css({width: '100%', height: '100%'});
    
    img.unbind("load");
    
    // Loadnew page
    img.attr('src',  window.book.path + "/pages/small-page-" + page + ".png");
}

function isChrome() {
    return window.navigator.userAgent.indexOf('Chrome') !== -1;
}

function disableControls(page) {
    if (page === 1){
        $('.previous-button').hide();
    }else{
        $('.previous-button').show();
    }
    
    if (page === $('.magazine').turn('pages')){
        $('.next-button').hide();
    }else{
        $('.next-button').show();
    }
}

// Set the width and height for the viewport
function resizeViewport() {
    var width = $(window).width(),
    height = $(window).height();
    
    var $magazine = $(".magazine"),
    $magazineViewPort = $(".magazine-viewport");
    
    $magazine.removeClass('animated');
    
    $magazineViewPort.css({
        width   : width,
        height  : height
    })
    .zoom("resize");

    if($magazine.turn("zoom") === 1){
        /*
         * Set landscape or vertical display
         */
        var display = width > height ? "double" : "single";
        var boundedSize = getBoundedSize();
        
        $magazine
        .turn("display", display)
        .turn("size", boundedSize.width, boundedSize.height);

        if($magazine.turn('page') === 1){
            $magazine.turn("peel", "tr");
        }
        
        $('.next-button').css({
            height: boundedSize.height, 
            backgroundPosition: '-38px ' + (boundedSize.height / 2 - 32 / 2) + 'px'
        });

        $('.previous-button').css({
            height: boundedSize.height, 
            backgroundPosition: '-4px ' + (boundedSize.height / 2 - 32 / 2) + 'px'
        });

        $magazine.css({top: -boundedSize.height / 2, left: -boundedSize.width / 2});
    }
    
    $magazine.addClass('animated');
}


// Number of views in a flipbook

function numberOfViews(book) {
    return book.turn('pages') / 2 + 1;
}

// Current view in a flipbook

function getViewNumber(book, page) {
    return parseInt((page || book.turn('page')) / 2 + 1, 10);
}

// Width of the flipbook when zoomed in
function largeMagazineWidth() {
    return 2214;
}

// decode URL Parameters

function decodeParams(data) {
    var parts = data.split('&'), d, obj = {};

    for (var i = 0; i < parts.length; i++) {
        d = parts[i].split('=');
        obj[decodeURIComponent(d[0])] = decodeURIComponent(d[1]);
    }
    
    return obj;
}

/*
 * Get bounded magazine size
 */
function getBoundedSize(){
    var width = $(window).width(),
    height = $(window).height();
    
    /*
     * Get ratio from page size
     */
    var ratio = window.book.pageWidth / window.book.pageHeight;
    var targetHeight = undefined;
    var targetWidth = undefined;
    
    if(width > height){ //landscape
        if(width / ratio > height && height * ratio * 2 > width){
            targetWidth = width;
            targetHeight = targetWidth / ratio / 2;
        }else{
            targetHeight = height;
            targetWidth = targetHeight * ratio * 2;
        }
    }else{ //vertical
        if(width / ratio > height && height * ratio < width){
            targetHeight = height;
            targetWidth = targetHeight * ratio;
        }else{
            targetWidth = width;
            targetHeight = targetWidth / ratio;
        }
    }
    
    return {
        width   : targetWidth,
        height  : targetHeight
    };
}

function selectThumbnail(page){
    var $thumbnails = $(".thumbnails");
    var $navBarPaginator = $("input[data-current-page]");
    
    $thumbnails.find("li.current")
    .removeClass('current');
    
    $thumbnails.find("li[data-page=" + page + "]")
    .addClass('current');
    
    $navBarPaginator.val(page);
}

/* Note */
function addNoteItem($container, noteId, description, color){
    var $template   = $("#note-item-template");
    var $item       = $template.children().clone();
    
    var $visiblePart    = $item.find(".visible");
    var $hiddenPart     = $item.find(".hidden");
    
    $item.css("backgroundColor", color);
    
    //visible part
    $visiblePart.find(".description").text(description);
    
    $visiblePart.find("button.delete").click(function(){
        $.get("/ebook/" + window.book.id + "/note/" + noteId + "/delete", function(success){
            if(success){
                $item.slideUp(function(){
                    $(this).remove();
                });
            }else{
                $.message("Ah ocurrido un error, vuelve a intentarlo");
            }
        });
    });
    
    $visiblePart.find("button.edit").click(function(){
        $visiblePart.hide();
        $hiddenPart.show();
    });
    
    //hidden part
    $hiddenPart.find("textarea").val(description);
    
    $hiddenPart.find("form")
    .attr("action", "/ebook/" + window.book.id + "/note/" + noteId)
    .send(function(success){
        if(success){
            $visiblePart.find(".description").text($hiddenPart.find("textarea").val());
            $hiddenPart.find("button.cancel").click();
        }else{
            $.message("Ah ocurrido un error al intentar guardar los datos");
        }
    });
    
    $hiddenPart.find("button.cancel").click(function(){
        $visiblePart.show();
        $hiddenPart.hide();
    });

    $container.prepend($item);
}

/*
 * Extensions App
 * Version 0.1
 */
(function($){
    $.message = function(stringMessage) {
        var $message = $("<div>").attr({
           class: "message-book" 
        })
        .text(stringMessage);

        var currentMessages = $("body .message-book");
        
        $message
        .css("bottom", (currentMessages.outerHeight(true) + 5) * currentMessages.length + 5)
        .appendTo("body")
        .fadeIn();
        
        setTimeout(function(){
            $message.fadeOut(function(){
                $(this).remove();
                
                var $messages = $("body .message-book");
                
                $messages.each(function(index){
                    $(this).animate({
                        bottom : ($(this).outerHeight(true) + 5) * index + 5
                    }, 300);
                });
            });
        }, 5000);
    };
}(jQuery));