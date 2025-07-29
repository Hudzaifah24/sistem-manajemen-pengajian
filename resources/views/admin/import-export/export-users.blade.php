<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Nama</th>
            <th>Email</th>
            <th>No Telp</th>
            <th>Gender</th>
            <th>Tanggal Lahir</th>
            <th>Tempat Lahir</th>
            <th>Alamat</th>
            <th>Kota</th>
            <th>Kelompok</th>
            <th>Password</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($users as $user)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td data-type="s">{{ $user->phone }}</td>
                <td>{{ $user->gender }}</td>
                <td>{{ $user->birth_date?->format('Y-m-d') }}</td>
                <td>{{ $user->birth_place }}</td>
                <td>{{ $user->address }}</td>
                <td>{{ $user->city }}</td>
                <td>{{ $user->division?->name }}</td>
                <td>{{ $user->raw_password }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
