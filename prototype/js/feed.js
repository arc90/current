


$(document).ready(function() {

    /**
     *
     * Variables
     * For strings and cached jQuery
     *
     */
    var $feed = $('.feed'),
        // Strings
        str_end_of_feed = 'That\'s All Folks!',
        str_could_not_load_feed = 'Sorry, your data could not be retrieved from the server <span class="icon icon-frown"></span>';

    /**
     *
     * Feed Object
     *
     */
    var Feed = {
        data: {},
        data_render: [],
        count_increment: 15,
        count_flag: 0,
        current_service: '',

        /**
         * Initialize the page
         */
        init: function(){
            
            // Remove the 'You need javascript' default HTML
            $feed.html('');
            $feed.addClass('is-loading');

            // Genereate the nav lists (<ul> for desktop, <select> for mobile)
            generate_nav_list(this.data);

            // Sort the data chronologically
            function custom_sort(a, b) {
                return a.date>b.date ? -1 : a.date<b.date ? 1 : 0;
            }
            this.data.sort(custom_sort);

            // Set the current service id to 'all'
            this.current_service = 'all';

            // Render the feed
            setTimeout(function(){
                Feed.render();
                $feed.removeClass('is-loading');
            }, 500);
        },

        /**
         * Render
         * Renders the feed on page load, or upon selecting an individual feed type
         */
        render: function() {
            
            // erase the feed
            $feed.html('');

            // If it's 'all' data, do a normal render
            // Otherwise, append each object by id to data_render 
            if ( this.current_service === 'all' ) {
                this.data_render = this.data;
                this.handlebars();
            } else {
                for(i=0;i<this.data.length;i++) {
                    if ( this.data[i].id === this.current_service ) {
                        this.data_render.push( this.data[i] );
                    }
                }
                this.handlebars();
            }  
        },

        render_more: function(){

            // Remove the more <li> element from the DOM
            $('.more').parent().remove();

            // Render more of the current service
            this.handlebars();

        },

        trim_data: function(){
            var data = [];
            data = this.data_render.slice(this.count_flag, this.count_flag+this.count_increment);
            this.count_flag += this.count_increment;
            return data;
        },

        handlebars: function() {
            // Setup variables
            var source,     // Source for Handlebars
                template,   // Tempalte for handlebars
                entries;    // Create an array for the data to use it in handlebars
            var html = '';  // HTML for all compiled handlebar templates

            // Compile handlebars template from data
            source = document.getElementById('template-feed').innerHTML;
            template = Handlebars.compile( source );

            html += template( {entries: this.trim_data() } );

            // Insert content into DOM
            console.log('Count: '+this.count_flag+', Remaining items: '+this.data_render.length);
            if (this.count_flag >= this.data_render.length) {
                // there's nothing left to render, don't supply a 'more' button
                $('.feed').append( html + '<li class="feed__item is-end">'+ str_end_of_feed +'</li>'); 
            } else {
                // there's still stuff to render, give a more button
                $('.feed').append( html + '<li class="feed__item is-bottom"></li>'); 
            }  
        }
    }
    

    /**
     *
     * Get Data and Initiate
     * Get the data from the server and initiate Feed
     *
     */
    $.ajax({
        url: "get_feed_data.php",
        async: false,
        dataType: 'json',
        success: function(result) {
            Feed.data = result;
            Feed.init();
        },
        error: function() {
            $feed.html('<li class="align-center">'+ str_could_not_load_feed +'</li>');
        }
    });

    /**
     *
     * Change Feed Data
     * Commands for changing the feed data from UI controls
     * This includes the <select> and <ul> navigation elements
     *
     */
    $('.nav-secondary select').on('change', function(ev){
        // Currently Select Item's ID
        var id = $(this).val();

        // Change the data in the feed
        on_feed_change(id);

        // change nav-list to current item
        $('.nav-secondary .is-active').removeClass('is-active');
        $('#'+id).parent().addClass('is-active');
        $(this).blur(); 
        
    });

    // Changed the feed data
    $('.nav-secondary ul a').on('click', function(ev){
        
        ev.preventDefault();

        // get the clicked element's id
        var _el = $(this);
        var id = _el.attr('id');
        
        // Change is-active classes
        $('.is-active', _el.parents('ul')).removeClass('is-active');
        _el.parent().addClass('is-active');

        // Change the data in the feed
        if (Feed.current_service == id) {
            ev.preventDefault();
            console.log('not reloading the feed, as we are on the current item');   
        } else {
            on_feed_change(id); 
        }

        // change <select> to current item
        $('.nav-secondary select').val(id);
    });
    
    function on_feed_change(id) {

        // Add loading class
        $feed.addClass('is-loading'); 

        // reset Feed class stuff
        Feed.current_service = id;
        Feed.count_flag = 0;
        Feed.data_render = [];

        setTimeout(nav_list_loading, 500);
        function nav_list_loading() {
            
            // render it
            Feed.render();
            
            // remove loading state
            $feed.removeClass('is-loading');
        }
    }

    // Bind click events for .load-more class
    // $('.feed').on('click', '.more', function(ev){
    //     console.log('fire click');
    //     ev.preventDefault();
    //     Feed.render_more();
    // });

    /**
     *
     * Infinite Scroll
     *************************
     * NOTE: This probably needs optimization, shouldn't attach directly to scroll
     *************************
     */


    $(window).scroll(function() {
        
            // Infinite scroll
            if($(window).scrollTop() >= $(document).height() - $(window).height() - 200) {
                
                if( !(Feed.count_flag >= Feed.data_render.length) ){
                    // Remove the last item in the feed
                    $($feed).children().last().remove();

                    // Render the next items
                    Feed.render_more();
                }
                
            }
    });

});




/**
 * Generate Navigation List
 * Generate a list of all the services in the feed from the feed data
 * Rendered using handlebars
 */
function generate_nav_list(data) {
    // Create a list of all the services we're using
    var feed_ids = [];    

    // Loop through the data. Check each item's 'id' against the services list
    // If it hasn't been added yet, add it so we have a unique list of services we're using
    $.each(data, function(i, el){
        if( $.inArray( el.id, feed_ids ) === -1 ) {
            feed_ids.push(el.id);
        }
    });

    // Compile handlebars template from data
    var template = Handlebars.compile( $('#template-nav-secondary').html() );
    var html = template( {services: feed_ids } );

    // Insert content into DOM
    $('.nav-secondary').append( html ); 
}


/**
 * Handlebars Helpers
 *
 * format_date() - formats date using timeago() jquery plugin
 * trim_description() - trims the content down to specified length
 */
Handlebars.registerHelper("format_date", function(date) {
    date = new Date( (date*1000) );
    date = $.timeago( date );
    //date = date.toISOString();
    return date;
});

Handlebars.registerHelper("trim_excerpt", function(description) {
    return description.substring(0, 140);
});

Handlebars.registerHelper('equal', function(lvalue, rvalue, options) {
    if( lvalue != rvalue ) {
        return options.inverse(this);
    } else {
        return options.fn(this);
    }
});

Handlebars.registerHelper("parse_twitter", function(description) {
    // cutoff username at beginning
    var cutoff = description.indexOf(':');
    var tweet = description.slice(cutoff+2);
    
    // find links in tweet and link them
    tweet = find_link_in_string(tweet);

    // find user names and link them
    var users = tweet.match(/@[\w]+/g);
    // replace usernames with links
    if (users) {
        for (var i = 0; i < users.length; i++) {
            tweet = tweet.replace(users[i], '<a href="http://twitter.com/'+users[i]+'">'+users[i]+'</a>');
        }
    }

    // find hashes and link them
    var hashes = tweet.match(/#[\w]+/g);
    // replace hashes with links
    if (hashes) {
        for (var i = 0; i < hashes.length; i++) {
            // format hash for URL
            var hash_for_url = hashes[i].replace('#', '%23');
            tweet = tweet.replace(hashes[i], '<a href="https://twitter.com/search?q='+hash_for_url+'">'+hashes[i]+'</a>');
        }
    }

    return tweet;
});

function find_link_in_string(text) {
    var exp = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
    return text.replace(exp,"<a href='$1'>$1</a>"); 
}

