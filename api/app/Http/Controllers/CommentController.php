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
            return response()->json([
                'status' => 'error',
                'message' => 'Mober não encontrado.',
            ], 404);
        }

        $comment = Comment::create([
            'momento_id' => $momentoId,
            'user_id' => $request->user()->id,
            'content' => $request->input('content'),
        ]);

        // Notifica que o post foi comentado
        $momento->usuario->notify(new MomentoCommented($request->user(), $momento->id));

        // Carrega o relacionamento do usuário para já retornar junto
        $comment->load('user:id,username,avatar');

        return response()->json([
            'status' => 'success',
            'message' => 'Comentário adicionado com sucesso!',
            'data' => [
                'id' => $comment->id,
                'content' => $comment->content,
                'created_at' => $comment->created_at->diffForHumans(),
                'user' => [
                    'id' => $comment->user->id,
                    'username' => $comment->user->username,
                    'avatar_url' => $comment->user->avatar_url
                ]
            ]
        ], 201);
    }

    public function index($momentoId)
    {
        $momento = Momento::find($momentoId);

        if (!$momento) {
            return response()->json([
                'status' => 'error',
                'message' => 'Mober não encontrado.',
            ], 404);
        }

        $comments = $momento->comments()
            ->with('user:id,username,avatar')
            ->latest()
            ->get()
            ->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'created_at' => $comment->created_at->diffForHumans(),
                    'user' => [
                        'id' => $comment->user->id,
                        'username' => $comment->user->username,
                        'avatar_url' => $comment->user->avatar_url
                    ]
                ];
            });

        return response()->json([
            'status' => 'success',
            'message' => 'Comentários carregados com sucesso.',
            'data' => $comments
        ], 200);
    }

    public function destroy(Request $request, $commentId)
    {
        $comment = Comment::find($commentId);

        if (!$comment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Comentário não encontrado.',
            ], 404);
        }

        if ($comment->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Não autorizado.',
            ], 403);
        }

        $comment->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Comentário deletado com sucesso.',
        ], 200);
    }

}
