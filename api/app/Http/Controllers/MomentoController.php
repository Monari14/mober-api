<?php

namespace App\Http\Controllers;

use App\Models\Momento;
use App\Notifications\MomentoLiked;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MomentoController extends Controller
{
    // Lista momentos do usuário logado, com fotos
    public function index(Request $request)
    {
        $momentos = Momento::with('fotos')
            ->where('user_id', auth()->id())
            ->latest('created_at')
            ->get();

        $momentosFormatados = $momentos->map(function ($momento) {
            return [
                'id' => $momento->id,
                'descricao' => $momento->descricao,
                'fotos' => $momento->fotos->map(function ($foto) {
                    return [
                        'id' => $foto->id,
                        'foto_url' => $foto->foto_url,
                    ];
                }),
            ];
        });

        return response()->json(['momentos' => $momentosFormatados]);
    }

    // Cria um momento e faz upload das fotos
    public function store(Request $request)
    {
        try {
            // Verifica se o usuário está autenticado
            $user = auth()->user();
            if (!$user) {
                return response()->json(['erro' => 'Usuário não autenticado.'], 401);
            }

            // Validação dos dados recebidos
            $validated = $request->validate([
                'descricao' => 'nullable|string',
                'fotos' => 'nullable|array',
                'fotos.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ]);

            // Criação do momento
            $momento = new Momento();
            $momento->user_id = $user->id;
            $momento->descricao = $validated['descricao'] ?? null;
            $momento->save();

            // Upload e associação das fotos, se houver
            if ($request->hasFile('fotos')) {
                foreach ($request->file('fotos') as $foto) {
                    if ($foto->isValid()) {
                        $caminho = $foto->store("m", 'public');
                        $momento->fotos()->create([
                            'caminho_arquivo' => $caminho,
                        ]);
                    } else {
                        return response()->json(['erro' => 'Uma das fotos é inválida.'], 422);
                    }
                }
            }

            // Carrega fotos associadas
            $momento->load('fotos');

            // Resposta de sucesso
            return response()->json([
                'mensagem' => 'Momento criado com sucesso!',
                'momento' => [
                    'id' => $momento->id,
                    'descricao' => $momento->descricao,
                    'imagens' => $momento->fotos->map(function ($foto) {
                        return [
                            'id' => $foto->id,
                            'foto_url' => $foto->foto_url,
                        ];
                    })
                ],
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'erro' => 'Erro de validação.',
                'detalhes' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'erro' => 'Erro inesperado ao criar momento.',
                'mensagem' => $e->getMessage()
            ], 500);
        }
    }


    // Mostrar detalhes de um momento (com fotos)
    public function show($id)
    {
        $momento = Momento::with('fotos')->findOrFail($id);

        // Verificar se o momento pertence ao usuário ou é público (se implementar)
        if ($momento->user_id !== auth()->id()) {
            return response()->json(['erro' => 'Acesso negado'], 403);
        }

        return response()->json($momento);
    }

    // Atualizar dados do momento (com tratamento igual ao store)
    public function update(Request $request, $id)
    {
        try {
            // Verifica autenticação
            $user = auth()->user();
            if (!$user) {
                return response()->json(['erro' => 'Usuário não autenticado.'], 401);
            }

            // Busca o momento
            $momento = Momento::findOrFail($id);

            // Verifica se pertence ao usuário
            if ($momento->user_id !== $user->id) {
                return response()->json(['erro' => 'Acesso negado'], 403);
            }

            // Validação
            $validated = $request->validate([
                'descricao' => 'nullable|string',
                'fotos' => 'nullable|array',
                'fotos.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ]);

            // Atualiza descrição
            $momento->descricao = $validated['descricao'] ?? $momento->descricao;
            $momento->save();

            // Upload de novas fotos (se houver)
            if ($request->hasFile('fotos')) {
                foreach ($request->file('fotos') as $foto) {
                    if ($foto->isValid()) {
                        $caminho = $foto->store("m", 'public');
                        $momento->fotos()->create([
                            'caminho_arquivo' => $caminho,
                        ]);
                    } else {
                        return response()->json(['erro' => 'Uma das fotos é inválida.'], 422);
                    }
                }
            }

            // Retorna atualizado
            $momento->load('fotos');

            return response()->json([
                'mensagem' => 'Momento atualizado com sucesso!',
                'momento' => [
                    'id' => $momento->id,
                    'descricao' => $momento->descricao,
                    'imagens' => $momento->fotos->map(fn($foto) => [
                        'id' => $foto->id,
                        'foto_url' => $foto->foto_url,
                    ]),
                ],
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'erro' => 'Erro de validação.',
                'detalhes' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'erro' => 'Erro inesperado ao atualizar momento.',
                'mensagem' => $e->getMessage()
            ], 500);
        }
    }

    // Deletar momento e fotos (com tratamento igual ao store)
    public function destroy($id)
    {
        try {
            // Verifica autenticação
            $user = auth()->user();
            if (!$user) {
                return response()->json(['erro' => 'Usuário não autenticado.'], 401);
            }

            // Busca o momento
            $momento = Momento::with('fotos')->findOrFail($id);

            // Verifica se pertence ao usuário
            if ($momento->user_id !== $user->id) {
                return response()->json(['erro' => 'Acesso negado'], 403);
            }

            // Apaga fotos do storage
            foreach ($momento->fotos as $foto) {
                Storage::disk('public')->delete($foto->caminho_arquivo);
                $foto->delete();
            }

            // Apaga o momento
            $momento->delete();

            return response()->json([
                'mensagem' => 'Momento removido com sucesso!'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'erro' => 'Erro inesperado ao remover momento.',
                'mensagem' => $e->getMessage()
            ], 500);
        }
    }


    public function like(Request $request, $postId)
    {
        $momento = Momento::find($postId);
        if (!$momento) {
            return response()->json(['message' => 'Mober não encontrado.'], 404);
        }

        $alreadyLiked = $momento->likes()->where('user_id', $request->user()->id)->exists();

        if ($alreadyLiked) {
            return response()->json(['message' => 'Você já curtiu este mober.'], 400);
        }

        $momento->likes()->create([
            'user_id' => $request->user()->id,
        ]);

        // Notifica o like
        $momento->usuario->notify(new MomentoLiked($request->user(), $momento->id));

        return response()->json(['message' => 'Mober curtido!']);
    }

    public function unlike(Request $request, $postId)
    {
        $momento = Momento::find($postId);
        if (!$momento) {
            return response()->json(['message' => 'Mober não encontrado.'], 404);
        }

        $like = $momento->likes()->where('user_id', $request->user()->id)->first();

        if (!$like) {
            return response()->json(['message' => 'Você não curtiu este mober.'], 400);
        }

        $like->delete();

        return response()->json(['message' => 'Curtida removida.']);
    }
}
