<?php

namespace App\Imports;

use App\Models\Division;
use App\Models\Education;
use App\Models\JobTitle;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;

class UsersImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    public function __construct(public bool $save = true)
    {
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $division_id = Division::where('name', $row['kelompok'])->first()?->id
            ?? Division::create(['name' => $row['kelompok']])?->id;
        $user = (new User)->forceFill([
            'id' => isset($row['id']) ? $row['id'] : null,
            'name' => $row['nama'],
            'email' => $row['email'],
            'phone' => $row['no_telp'],
            'gender' => $row['gender'] == 'laki-laki' ? 'male' : 'female',
            'birth_date' => $row['tanggal_lahir'],
            'birth_place' => $row['tempat_lahir'],
            'address' => $row['alamat'],
            'city' => $row['kota'],
            'division_id' => $division_id,
            'password' => $row['password'] ? Hash::make($row['password']) : Hash::make('password'),
            'raw_password' => $row['password'] ?? 'password',
            'group' => 'user',
        ]);
        if ($this->save) {
            $user->save();
        }
        return $user;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', Rule::unique('users', 'email')],
        ];
    }

    public function onFailure(Failure ...$failures)
    {
    }
}
