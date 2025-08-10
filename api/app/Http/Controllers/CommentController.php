<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Momento;
use Illuminate\Http\Request;
use App\Notifications\MomentoCommented;

class CommentController extends Controller
{
    public function store(Request $request, $momentoId)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $momento = Momento::find($momentoId);

        if (!$momento) {
            return response()->json(['message' => 'Mober não encontrado.'], 404);
        }

        $comment = Comment::create([
            'momento_id' => $momentoId,
            'user_id' => $request->user()->id,
            'content' => $request->input('content'),
        ]);

        // Notifica que o post foi comentado
        $momento->user->notify(new MomentoCommented($request->user(), $momento->id));

        return response()->json([
            'message' => 'Comentário adicionado com sucesso!',
            'comment' => $comment->load('user:id,username'),
        ]);
    }

    public function index($momentoId)
    {
        $momento = Momento::find($momentoId);

        if (!$momento) {
            return response()->json(['message' => 'Mober não encontrado.'], 404);
        }

        $comments = $momento->comments()->with('user:id,username')->latest()->get();

        return response()->json($comments);
    }

    public function destroy(Request $request, $commentId)
    {
        $comment = Comment::find($commentId);

        if (!$comment) {
            return response()->json(['message' => 'Comentário não encontrado.'], 404);
        }

        if ($comment->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Não autorizado.'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Comentário deletado com sucesso.']);
    }
}
