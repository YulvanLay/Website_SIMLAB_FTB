@extends('layouts.app')

@section('title', 'Daftar Bebas Lab')

@section("content")

    <div class="row">
        <div class="col-sm-6">
            <h2>Bebas Laboratorium</h2>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <label for="pelanggan">Pelanggan:</label>
            <select id="pelanggan" class="select2">
                <option selected disabled hidden>-- Pilih Pelanggan --</option>
                @foreach($pelanggans as $pelanggan)
                    @if(auth()->user()->laboran || auth()->user()->koordinator || auth()->user()->kalab)
                        <option value="{{ $pelanggan->kode_pelanggan }}">{{ $pelanggan->nama_pelanggan }}</option>
                    @elseif(auth()->user()->pelanggan)
                        @if(auth()->user()->pelanggan->kode_pelanggan == $pelanggan->kode_pelanggan)
                            <option value="{{ $pelanggan->kode_pelanggan }}">{{ $pelanggan->nama_pelanggan }}</option>
                        @endif
                    @endif
                @endforeach
            </select>

        </div>
    </div><br>

    <div class="row" style="margin-top: 20px;">
        <div class="col-sm-12">
            <table id="tabel-pemakaian" class="datatable stripe hover row-border order-column cell-border"
                style="width:100%">
                <thead>
                    <tr>
                        <th>Laboratorium</th>
                        <th>Preview</th>
                        <th>Acc Laboran</th>
                        <th>Acc Kalab</th>
                        <th>Form Bebas Lab</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>


 <script type="text/javascript" src="{{ URL::asset('js/custom-data-table.js') }}"></script>

    <script type="text/javascript">

        var kode_pelanggan;
        var currentBebasLabId;

        $(document).ready(function () {

            var dt = $('#tabel-pemakaian').DataTable(tableOptions);

            // inisialisasi select2
            $('.select2').select2();

            // saat pelanggan dipilih
            $('#pelanggan').on('change', function () {

                console.log('change jalan');

                kode_pelanggan = $(this).val();

                console.log(kode_pelanggan);

                $.ajax({

                    type: 'GET',

                    url: '/bebas-lab/' + kode_pelanggan,

                    success: function (data) {

                        console.log(data);

                        dt.clear();

                        var rows = data.data;

                        if (rows && rows.length > 0) {

                            for (var i = 0; i < rows.length; i++) {

                                var row = rows[i];

                                var btnPreview =
                                    "<button class='btn btn-info btn-sm' onclick='openPreview(" + row.id + ")'>"
                                    + "<i class='fas fa-eye'></i> Preview</button>";

                                var btnAccLaboran =
                                    "<button class='btn btn-warning btn-sm' onclick='openAccLaboran(" + row.id + ")'>"
                                    + "<i class='fas fa-check'></i> Acc Laboran</button>";

                                var btnAccKalab =
                                    "<button class='btn btn-success btn-sm' onclick='openAccKalab(" + row.id + ")'>"
                                    + "<i class='fas fa-check-double'></i> Acc Kalab</button>";

                                var btnForm =
                                    "<button class='btn btn-primary btn-sm' onclick='openFormBebasLab(" + row.id + ")'>"
                                    + "<i class='fas fa-file-alt'></i> Form</button>";

                                var namaLab = '-';

                                if (row.laboratorium) {
                                    namaLab = row.laboratorium.nama_laboratorium;
                                }

                                var d =
                                    "<tr>"
                                    + "<td>" + namaLab + "</td>"
                                    + "<td>" + btnPreview + "</td>"
                                    + "<td>" + btnAccLaboran + "</td>"
                                    + "<td>" + btnAccKalab + "</td>"
                                    + "<td>" + btnForm + "</td>"
                                    + "</tr>";

                                dt.row.add($(d).get(0));
                            }

                            dt.draw();
                        }
                        else {

                            dt.draw();

                            console.log('data kosong');
                        }
                    },

                    error: function (xhr, status, error) {

                        console.log(xhr);
                        console.log(status);
                        console.log(error);

                        alert('Gagal mengambil data bebas lab.');
                    }
                });

            });

        });

        // Function untuk membuka preview
        function openPreview(id) {
            window.location.href = '/bebas-lab-preview/' + id;
        }

        // Function untuk membuka acc laboran (jika ada)
        function openAccLaboran(id) {
            window.location.href = '/bebas-lab-preview/' + id;
        }

        // Function untuk membuka acc kalab (jika ada)
        function openAccKalab(id) {
            window.location.href = '/bebas-lab-preview/' + id;
        }

        // Function untuk membuka form bebas lab (jika ada)
        function openFormBebasLab(id) {
            alert('Fitur form belum tersedia');
        }
    </script>

@endsection


   