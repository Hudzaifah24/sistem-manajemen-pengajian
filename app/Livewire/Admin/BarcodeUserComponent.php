<?php

namespace App\Livewire\Admin;

use App\ExtendedCarbon;
use App\Models\Attendance;
use App\Models\Barcode;
use App\Models\Shift;
use Ballen\Distical\Entities\LatLong;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Laravel\Jetstream\InteractsWithBanner;
use Ballen\Distical\Calculator as DistanceCalculator;
use Livewire\Component;

class BarcodeUserComponent extends Component
{
    use InteractsWithBanner;

    public $deleteName = null;
    public $confirmingDeletion = false;
    public $selectedId = null;

    public function confirmDeletion($id, $name)
    {
        $this->deleteName = $name;
        $this->confirmingDeletion = true;
        $this->selectedId = $id;
    }

    public function delete()
    {
        if (Auth::user()->isNotAdmin) {
            return abort(403);
        }
        $barcode = Barcode::find($this->selectedId);
        $barcode->delete();
        $this->confirmingDeletion = false;
        $this->selectedId = null;
        $this->deleteName = null;
        $this->banner(__('Deleted successfully.'));
    }

    public function render()
    {
        $barcodes = Barcode::whereNotNull('user_id')->get();
        return view('livewire.admin.barcode-user', [
            'barcodes' => $barcodes,
        ]);
    }
}
