<?php

namespace App\Http\Controllers;

use App\Models\Momento;
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

    // Atualizar dados do momento (sem fotos aqui)
    public function update(Request $request, $id)
    {
        $momento = Momento::findOrFail($id);

        if ($momento->user_id !== auth()->id()) {
            return response()->json(['erro' => 'Acesso negado'], 403);
        }

        $request->validate([
            'titulo' => 'sometimes|required|string|max:255',
            'descricao' => 'nullable|string',
            'data' => 'sometimes|required|date',
            'sentimento' => 'nullable|string|max:50',
            'local' => 'nullable|string|max:255',
        ]);

        $momento->fill($request->only([
            'titulo',
            'descricao',
            'data',
            'sentimento',
            'local',
        ]));

        $momento->save();

        return response()->json([
            'mensagem' => 'Momento atualizado com sucesso!',
            'momento' => $momento
        ]);
    }

    // Deletar momento e suas fotos
    public function destroy($id)
    {
        $momento = Momento::with('fotos')->findOrFail($id);

        if ($momento->user_id !== auth()->id()) {
            return response()->json(['erro' => 'Acesso negado'], 403);
        }

        // Apagar fotos do storage
        foreach ($momento->fotos as $foto) {
            Storage::disk('public')->delete($foto->caminho_arquivo);
            $foto->delete();
        }

        $momento->delete();

        return response()->json(['mensagem' => 'Momento removido com sucesso!']);
    }
}
