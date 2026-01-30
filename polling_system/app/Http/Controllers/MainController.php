
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MainController extends Controller
{
    public function loginView()
    {
        return view('login');
    }

    public function login(Request $r)
    {
        if (Auth::attempt($r->only('email', 'password'))) {
            return response()->json([
                'success' => true
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => 'Invalid email or password'
        ]);
    }

    public function polls()
    {
        $polls = DB::table('polls')
            ->where('status', 'active')
            ->get();

        return view('polls', compact('polls'));
    }

    public function pollView($id)
    {
        $poll = DB::table('polls')->find($id);
        $options = DB::table('options')
            ->where('poll_id', $id)
            ->get();

        return view('poll_view', compact('poll', 'options'));
    }

    public function vote(Request $r)
    {
        $ip = $_SERVER['REMOTE_ADDR'];

        $alreadyVoted = DB::table('votes')
            ->where('poll_id', $r->poll_id)
            ->where('ip_address', $ip)
            ->exists();

        if ($alreadyVoted) {
            return response()->json([
                'error' => 'You already voted from this IP'
            ]);
        }

        DB::table('votes')->insert([
            'poll_id'    => $r->poll_id,
            'option_id'  => $r->option_id,
            'ip_address' => $ip,
            'voted_at'   => now()
        ]);

        DB::table('vote_history')->insert([
            'poll_id'    => $r->poll_id,
            'option_id'  => $r->option_id,
            'ip_address' => $ip,
            'action'     => 'voted',
            'timestamp'  => now()
        ]);

        return response()->json([
            'success' => true
        ]);
    }

   
    public function results($id)
    {
        return DB::table('options')
            ->leftJoin('votes', 'options.id', '=', 'votes.option_id')
            ->where('options.poll_id', $id)
            ->groupBy('options.id', 'options.option_text')
            ->select(
                'options.option_text',
                DB::raw('COUNT(votes.id) as total')
            )
            ->get();
    }

 
    public function admin()
    {
        $votes = DB::table('votes')->get();
        $history = DB::table('vote_history')
            ->orderBy('timestamp')
            ->get();

        return view('admin', compact('votes', 'history'));
    }

  
    public function release(Request $r)
    {
        $vote = DB::table('votes')->find($r->vote_id);

        if (!$vote) {
            return response()->json([
                'error' => 'Vote not found'
            ]);
        }

        DB::table('votes')
            ->where('id', $r->vote_id)
            ->delete();

        DB::table('vote_history')->insert([
            'poll_id'    => $vote->poll_id,
            'option_id'  => $vote->option_id,
            'ip_address' => $vote->ip_address,
            'action'     => 'released',
            'timestamp'  => now()
        ]);

        return response()->json([
            'success' => true
        ]);
    }
}
