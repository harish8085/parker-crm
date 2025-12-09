<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{env('APP_NAME')}}</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" type="image/x-icon" href="{{asset('assets/images/favicon.webp')}}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Latest compiled JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    

    <!-- script of xlsx for export table in excel -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.2/xlsx.full.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- <link rel="stylesheet" href="path/to/font-awesome/css/font-awesome.min.css"> -->
    <link rel="stylesheet" href="{{asset('assets/css/app.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/responsive-fixes.css')}}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/1.11.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.7.1/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />


    @yield('style')

</head>

<body>
    <div class="dashboard-container">
        <!-- sidebar -->
        @include('Layout.sidebar')
        <!---------sidebar for mobile view ------------->
        @include('Layout.mb-navbar')
        <!-- dashboard -->
        <div class="main-container">
            <!-- /# navbar -->
            @include('Layout.navbar')
            <div class="dashboard-content">
                @yield('body')
            </div>
        </div>
    </div>
    @yield('modal')

    <!-- Global Announcement Modal -->
    <div class="modal fade" id="announcementModal" tabindex="-1" aria-labelledby="announcementModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="border-radius: 10px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
                <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 10px 10px 0 0; padding: 20px;">
                    <h4 class="modal-title mb-0" id="announcementModalTitle" style="font-weight: 600; font-size: 1.5rem;">
                        <i class="fas fa-bullhorn me-2"></i>Announcement
                    </h4>
                </div>
                <div class="modal-body" id="announcementModalBody" style="max-height: 60vh; overflow-y: auto; padding: 25px;">
                </div>
                <div class="modal-footer" style="border-top: 1px solid #dee2e6; padding: 15px 25px;">
                    <button type="button" id="announcementOkButton" class="btn btn-primary btn-lg px-5" style="border-radius: 5px; font-weight: 500;">
                        <i class="fas fa-check me-2"></i>OK
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        /* Ensure modal is centered vertically */
        #announcementModal.modal {
            padding: 0 !important;
        }
        
        #announcementModal .modal-dialog-centered {
            min-height: calc(100% - 3.5rem);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .announcement-content {
            padding: 10px 0;
        }
        
        .announcement-message {
            line-height: 1.8;
            color: #495057;
            font-size: 15px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #667eea;
            margin-bottom: 20px;
        }
        
        .announcement-attachments {
            border-top: 2px solid #e9ecef;
            padding-top: 20px;
            margin-top: 20px;
        }
        
        .announcement-attachments strong {
            color: #495057;
            font-size: 16px;
            display: block;
            margin-bottom: 15px;
        }
        
        .attachment-item {
            display: flex;
            align-items: center;
            gap: 15px;
            transition: all 0.3s ease;
            border: 1px solid #dee2e6 !important;
            border-radius: 8px !important;
            padding: 15px !important;
            background-color: #fff;
            margin-bottom: 12px;
        }
        
        .attachment-item:hover {
            background-color: #f8f9fa;
            border-color: #667eea !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
        }
        
        .attachment-preview-img {
            cursor: pointer;
            transition: transform 0.3s ease;
            border: 2px solid #dee2e6;
        }
        
        .attachment-preview-img:hover {
            transform: scale(1.1);
            border-color: #667eea;
        }
        
        .attachment-name {
            word-break: break-word;
            color: #495057;
            font-size: 14px;
        }
        
        .attachment-item .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 5px;
            transition: all 0.3s ease;
            padding: 6px 15px;
        }
        
        .attachment-item .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.4);
        }
        
        .attachment-item i.fas {
            transition: transform 0.3s ease;
        }
        
        .attachment-item:hover i.fas {
            transform: scale(1.2);
        }
        
        #announcementModal .modal-header {
            border-bottom: none;
        }
        
        #announcementModal .modal-footer {
            border-top: 1px solid #dee2e6;
        }
    </style>
</body>



<script src="{{asset('assets/js/app.js')}}"></script>
<script src="{{asset('assets/js/mobile-nav.js')}}"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{asset('assets/js/apiService.js')}}"></script>
<!-- Moment.js -->
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<!-- Date Range Picker JS -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js"></script>
<!-- JSZip for Excel export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.6.0/jszip.min.js"></script>

<!-- PDFMake for PDF export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.68/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.68/vfs_fonts.js"></script>

<!-- Buttons HTML5 for export -->
<script src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.html5.min.js"></script>

<!-- Buttons print for export -->
<script src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.print.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

@yield('script')

<script src="{{ asset('assets/js/announcement-popup.js') }}"></script>


</html>