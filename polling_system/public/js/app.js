
function login(){
 $.post('/login',{email:$('#email').val(),password:$('#password').val(),_token:$('meta[name=csrf-token]').attr('content')},
 res=>{ if(res.success) location.href='/polls'; else $('#msg').text(res.error); });
}
function loadPoll(id){ $.get('/poll/'+id,res=>$('#content').html(res)); }
function vote(pollId,optionId){
 $.post('/vote',{poll_id:pollId,option_id:optionId,_token:$('meta[name=csrf-token]').attr('content')},
 res=>$('#message').text(res.error ?? 'Vote submitted'));
}
setInterval(()=>{
 let id=$('#pollId').val(); if(!id) return;
 $.get('/results/'+id,data=>{
  let h=''; data.forEach(r=>h+=`<p>${r.option_text}: ${r.total}</p>`);
  $('#results').html(h);
 });
},1000);
function release(id){
 $.post('/release',{vote_id:id,_token:$('meta[name=csrf-token]').attr('content')},()=>location.reload());
}
