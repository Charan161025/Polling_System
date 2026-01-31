function login(){
    var email = $('#email').val();
    var password = $('#password').val();
    
    $.ajax({
        url: '/login',
        type: 'POST',
        data: {
            email: email,
            password: password
        },
        dataType: 'json',
        success: function(res){
            console.log('Login response:', res); // Debug
            if(res.success) {
                window.location.href = '/polls';
            } else {
                $('#msg').text(res.error || 'Login failed');
            }
        },
        error: function(xhr, status, error){
            console.error('Login error:', error);
            $('#msg').text('Login failed. Please try again.');
        }
    });
    return false;
}

function loadPoll(id){
    $.get('/poll/' + id, function(res){
        $('#content').html(res);
        startResultsPolling();
    });
    return false;
}

function vote(pollId, optionId){
    $.ajax({
        url: '/vote',
        type: 'POST',
        data: {
            poll_id: pollId,
            option_id: optionId
        },
        dataType: 'json',
        success: function(res){
            var msgDiv = $('#message');
            if(res.error) {
                msgDiv.removeClass('alert-success').addClass('alert-danger').text(res.error).show();
            } else {
                msgDiv.removeClass('alert-danger').addClass('alert-success').text('Vote submitted successfully!').show();
                loadResults(pollId);
            }
        }
    });
}

function loadResults(pollId){
    $.get('/results/' + pollId, function(data){
        var html = '';
        if(data && data.length > 0) {
            data.forEach(function(r){
                html += '<p><strong>' + r.option_text + ':</strong> ' + r.total + ' votes</p>';
            });
        }
        $('#results').html(html);
    });
}

var resultsInterval;
function startResultsPolling(){
    clearInterval(resultsInterval);
    resultsInterval = setInterval(function(){
        var id = $('#pollId').val();
        if(id) {
            loadResults(id);
        }
    }, 1000);
}

function release(id){
    $.post('/release', {
        vote_id: id
    }, function(){
        location.reload();
    });
}
