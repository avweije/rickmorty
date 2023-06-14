/**
 * RickMortyHandler - a JS object to handle the page
 */
var RickMortyHandler = 
{
    /**
     * init - add event handlers
     */
    init() 
    {
        // attach the onchange handlers
        $( "#episodeSelect" ).on( "change", function() {
            // get the selected value
            var val = $(this).val();

            // deselect the other select boxes
            $('#locationSelect').val('');
            $('#dimensionSelect').val('');

            // get the characters
            RickMortyHandler.getCharacters({ "episode": val });
        });
    
        $( "#locationSelect" ).on( "change", function() {
            // get the selected value
            var val = $(this).val();

            // deselect the other select boxes
            $('#episodeSelect').val('');
            $('#dimensionSelect').val('');

            // get the characters
            RickMortyHandler.getCharacters({ "location": val });
        });
    
        $( "#dimensionSelect" ).on( "change", function() {
            // get the selected value
            var val = $(this).val();

            // deselect the other select boxes
            $('#episodeSelect').val('');
            $('#locationSelect').val('');

            // get the characters
            RickMortyHandler.getCharacters({ "dimension": val });
        });

        // bind the spinner to the AJAX events
        $( document ).on( "ajaxStart", function() {
            $('#rickmorty-overlay').fadeIn(100);
        }).on('ajaxStop', function(){
            $('#rickmorty-overlay').fadeOut(100);
        });
    },

    /**
     * getCharacters - gets the characters for an episode, location or dimension
     * 
     * @param mixed data
     */
    getCharacters(data)
    {
        $.ajax({
            url: '{{ (path('/')) }}',
            type: "POST",
            data: data,
            async: true
        })
        .done(function (data)
        {
            $('#charactersContainer').html(data);
        })
        .fail(function (err)
        {
            console.log(err);
        });
    }
}

// once the page has loaded
$(function() {
    // initialise the page
    RickMortyHandler.init();
});