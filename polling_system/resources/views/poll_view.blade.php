<div class="container mt-4">
    <h3>{{ $poll['question'] }}</h3>
    <input type="hidden" id="pollId" value="{{ $poll['id'] }}">
    
    @foreach($options as $o)
    <button onclick="vote({{ $poll['id'] }}, {{ $o['id'] }})" class="btn btn-secondary mb-2">
        {{ $o['option_text'] }}
    </button><br>
    @endforeach
    
    <div id="message" class="alert mt-3" style="display:none;"></div>
    
    <hr>
    <h5>Live Results</h5>
    <div id="results"></div>
</div>
