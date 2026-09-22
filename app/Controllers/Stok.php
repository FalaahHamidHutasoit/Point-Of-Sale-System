<?php

namespace App\Controllers;

use App\Models\MutasiStokModel;

class Stok extends BaseController
{
    public function index()
    {
        $keyword = trim((string) $this->request->getGet('keyword'));
        $tipe = trim((string) $this->request->getGet('tipe'));
        $model = new MutasiStokModel();

        $builder = $model
            ->select('mutasi_stok.*, barang.kode_barang, barang.nama_barang, barang.satuan, users.nama_lengkap')
            ->join('barang', 'barang.id_barang = mutasi_stok.id_barang')
            ->join('users', 'users.id_user = mutasi_stok.id_user');

        if ($keyword !== '') {
            $builder->groupStart()
                ->like('barang.kode_barang', $keyword)
                ->orLike('barang.nama_barang', $keyword)
                ->orLike('mutasi_stok.keterangan', $keyword)
                ->groupEnd();
        }

        if (in_array($tipe, ['MASUK', 'KELUAR', 'PENYESUAIAN'], true)) {
            $builder->where('mutasi_stok.tipe', $tipe);
        }

        return view('stok/index', [
            'title' => 'Mutasi Stok',
            'mutasi' => $builder->orderBy('mutasi_stok.id_mutasi', 'DESC')->paginate(25, 'mutasi'),
            'pager' => $model->pager,
            'keyword' => $keyword,
            'tipe' => $tipe,
        ]);
    }
}
