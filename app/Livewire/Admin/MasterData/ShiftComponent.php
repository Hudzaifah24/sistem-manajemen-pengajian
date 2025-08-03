<?php

namespace App\Livewire\Admin\MasterData;

use App\Livewire\Forms\ShiftForm;
use App\Models\Barcode;
use App\Models\Shift;
use Laravel\Jetstream\InteractsWithBanner;
use Livewire\Component;

class ShiftComponent extends Component
{
    use InteractsWithBanner;

    public ShiftForm $form;
    public Barcode $barcode;
    public $deleteName = null;
    public $creating = false;
    public $editing = false;
    public $confirmingDeletion = false;
    public $selectedId = null;
    public $qrDetail = null;

    public function showCreating()
    {
        $this->form->resetErrorBag();
        $this->form->reset();
        $this->creating = true;
    }

    public function showBarcode($id)
    {
        $barcode = Barcode::where('shift_id', $id)->first();
        $this->barcode = $barcode;
        $this->qrDetail = true;
    }

    public function create()
    {
        $this->form->store();
        $this->creating = false;
        $this->dispatch('reload-page');
        $this->banner(__('Created successfully.'));
    }

    public function edit($id)
    {
        $this->form->resetErrorBag();
        $this->editing = true;
        /** @var Shift $shift */
        $shift = Shift::find($id);
        $this->form->setShift($shift);
    }

    public function update()
    {
        $this->form->update();
        $this->editing = false;
        $this->dispatch('reload-page');
        $this->banner(__('Updated successfully.'));
    }

    public function confirmDeletion($id, $name)
    {
        $this->deleteName = $name;
        $this->confirmingDeletion = true;
        $this->selectedId = $id;
    }

    public function delete()
    {
        $shift = Shift::find($this->selectedId);
        $barcode = Barcode::where('user_id', $shift->id)->first();
        $barcode->delete();
        $this->form->setShift($shift)->delete();
        $this->confirmingDeletion = false;
        $this->banner(__('Deleted successfully.'));
    }

    public function render()
    {
        $shifts = Shift::all();
        return view('livewire.admin.master-data.shift', ['shifts' => $shifts]);
    }
}
