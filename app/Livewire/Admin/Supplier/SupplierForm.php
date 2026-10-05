<?php

namespace App\Livewire\Admin\Supplier;

use App\Models\Supplier;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class SupplierForm extends Component
{
    public ?Supplier $supplier = null;

    public $nama = '';

    public $hp = '';

    public $alamat = '';

    protected function rules()
    {
        return [
            'nama' => 'required|string|max:255',
            'hp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
        ];
    }

    public function mount(?Supplier $supplier = null)
    {
        if ($supplier && $supplier->exists) {
            Gate::authorize('update', $supplier);
            $this->supplier = $supplier;
            $this->nama = $supplier->nama;
            $this->hp = $supplier->hp;
            $this->alamat = $supplier->alamat;
        } else {
            Gate::authorize('create', Supplier::class);
        }
    }

    public function save()
    {
        $this->validate();

        $data = [
            'nama' => $this->nama,
            'hp' => $this->hp,
            'alamat' => $this->alamat,
        ];

        if ($this->supplier && $this->supplier->exists) {
            $this->supplier->update($data);
            session()->flash('message', 'Supplier berhasil diperbarui.');
        } else {
            Supplier::create($data);
            session()->flash('message', 'Supplier berhasil ditambahkan.');
        }

        return $this->redirect(route('admin.supplier.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.supplier.supplier-form')->layout('components.admin-layout');
    }
}
