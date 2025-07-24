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
            ->orderBy('data', 'desc')
            ->get();

        return response()->json($momentos);
    }

    // Cria um momento e faz upload das fotos
    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'data' => 'required|date',
            'sentimento' => 'nullable|string|max:50',
            'local' => 'nullable|string|max:255',
            'fotos.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $momento = new Momento();
        $momento->user_id = auth()->id();
        $momento->titulo = $request->titulo;
        $momento->descricao = $request->descricao;
        $momento->data = $request->data;
        $momento->sentimento = $request->sentimento;
        $momento->local = $request->local;
        $momento->save();

        // Upload fotos
        if ($request->hasFile('fotos')) {
            foreach ($request->file('fotos') as $foto) {
                $caminho = $foto->store("momentos/{$momento->id}", 'public');
                $momento->fotos()->create([
                    'caminho_arquivo' => $caminho,
                ]);
            }
        }

        // Carregar fotos no momento para retornar
        $momento->load('fotos');

        return response()->json([
            'mensagem' => 'Momento criado com sucesso!',
            'momento' => $momento
        ], 201);
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

    public function publicosPorUsuario($usuarioId)
    {
        // Buscar momentos do usuário com filtro de visibilidade pública
        $momentos = Momento::with('fotos')
            ->where('user_id', $usuarioId)
            ->where('visibilidade', 'publico') // assumindo campo visibilidade
            ->orderBy('data', 'desc')
            ->get();

        if ($momentos->isEmpty()) {
            return response()->json(['mensagem' => 'Nenhum momento público encontrado para este usuário.'], 404);
        }

        return response()->json($momentos);
    }
}
