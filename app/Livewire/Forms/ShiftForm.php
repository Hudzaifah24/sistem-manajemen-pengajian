<?php

namespace App\Livewire\Forms;

use App\Models\Barcode;
use App\Models\Shift;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Form;

class ShiftForm extends Form
{
    public ?Shift $shift;
    public ?Barcode $barcode;

    public $name = '';
    public $date = '';
    public $start_time = null;
    public $end_time = null;

    public function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                // Rule::unique('shifts')->ignore($this->shift)
            ],
            'date' => ['required'],
            'start_time' => ['required'],
            'end_time' => ['nullable'],
        ];
    }

    public function setShift(Shift $shift)
    {
        $this->shift = $shift;
        $this->name = $shift->name;
        $this->date = $shift->date;
        $this->start_time = $shift->start_time;
        $this->end_time = $shift->end_time;
        return $this;
    }

    public function setBarcode(Barcode $barcode)
    {
        $this->barcode = $barcode;
        return $this;
    }

    public function store()
    {
        if (Auth::user()->isNotAdmin) {
            return abort(403);
        }
        $this->validate();
        $shift = Shift::create($this->all());

        Barcode::create([
            'name' => $shift->name,
            'value' => rand(1111111111111, 9999999999999),
            'radius' => 300,
            'shift_id' => $shift->id
        ]);

        $this->reset();
    }

    public function update()
    {
        if (Auth::user()->isNotAdmin) {
            return abort(403);
        }
        $this->validate();
        $this->shift->update($this->all());
        $this->reset();
    }

    public function delete()
    {
        if (Auth::user()->isNotAdmin) {
            return abort(403);
        }
        $this->shift->delete();
        $this->reset();
    }
}
