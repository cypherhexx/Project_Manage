<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Comment;


class CommentController extends Controller
{
    

    public function destroy(Comment $comment)
    {

        $comment->delete();

        session()->flash('message', __('form.success_delete'));
        return  redirect()->back();
    }
}
