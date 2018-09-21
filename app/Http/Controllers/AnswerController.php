<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Answer;
use Illuminate\Support\Facades\DB;

class AnswerController extends Controller
{
    public function index()
    {
        $answers = Answer::all();

        return response()->json($answers);
    }

    public function average()
    {
        $avgG = round(Answer::all()->average('g'));
        $avgB = round(Answer::all()->average('b'));

        return response()->json(['r' => 0, 'g' => $avgG, 'b' => $avgB]);
    }

    public function answer(Request $request, $color)
    {
        $answer = new Answer;
    
        $answer->answer = $color;
        $answer->r = $request->input('r');
        $answer->g = $request->input('g');
        $answer->b = $request->input('b');
        $answer->poll = 'green/blue';

        $answer->save();

        return response('success', 200);
    }
}
