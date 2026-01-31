
function login(){
    $.post('/login', {
        email: $('#email').val(),
        password: $('#password').val()
    }, function(res){
        if(res.success) {
            location.href = '/polls';
        } else {
            $('#msg').text(res.error);
        }
    });
}

function loadPoll(id){
    $.get('/poll/' + id, function(res){
        $('#content').html(res);
        startResultsPolling();
    });
}

function vote(pollId, optionId){
    $.post('/vote', {
        poll_id: pollId,
        option_id: optionId
    }, function(res){
        var msgDiv = $('#message');
        if(res.error) {
            msgDiv.removeClass('alert-success').addClass('alert-danger').text(res.error).show();
        } else {
            msgDiv.removeClass('alert-danger').addClass('alert-success').text('Vote submitted successfully!').show();
            loadResults(pollId);
        }
    });
}

function loadResults(pollId){
    $.get('/results/' + pollId, function(data){
        var html = '';
        data.forEach(function(r){
            html += '<p><strong>' + r.option_text + ':</strong> ' + r.total + ' votes</p>';
        });
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
