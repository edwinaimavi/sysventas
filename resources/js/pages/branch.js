var divLoading = document.getElementById('divLoading');
let tableBranch;

document.addEventListener("DOMContentLoaded", function () {
     // CSRF token para AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // GUARDAR / ACTUALIZAR
    // GUARDAR / ACTUALIZAR
    $('#branchForm').on('submit', function (e) {
        e.preventDefault();
        divLoading.style.display = "flex"; // Mostrar el loading
        const $form = $(this);
        const id = $form.attr('data-id'); 
 
        let url = '';
        let type = '';
        //importante para subir imagenes/ eliminar el serializador de datos
        const formData = new FormData(this);
        if (id) {
            url = "/admin/branches/" + id;
            type = 'POST';
            formData.append('_method', 'PUT'); // Laravel necesita esto
        } else {
            url = window.routes.storeBranch;
            type = 'POST';
        }

        

        $.ajax({
            url: url,
            type: type,
            data: formData,
            processData: false, // necesario para enviar FormData
            contentType: false, // necesario para enviar FormData
            success: function (response) {
                divLoading.style.display = "none"; // Ocultar el loading
                $('#branchModal').modal('hide');
                tableBranch.ajax.reload(null, false);
                Swal.fire({
                    title: response.message,
                    icon: "success", // o "error", según el contexto
                    toast: true,
                    position: "top-end", // puedes cambiar a "bottom-end", "top-start", etc.
                    showConfirmButton: false,
                    timer: 3000, // duración en milisegundos
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer);
                        toast.addEventListener('mouseleave', Swal.resumeTimer);
                    }
                });

            },
            error: function (xhr) {
                divLoading.style.display = "none"; // Ocultar el loading
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    let errorList = '<ul>';
                    $.each(errors, function (key, messages) {
                        errorList += `<li>${messages[0]}</li>`;
                    });
                    errorList += '</ul>';
                    $('#error-messages').removeClass('d-none').html(errorList);
                }
            }
        });
    });

    $(document).on('click','.editBranch', function(){
        //PONEMOS EN UNA VARIABLE LA DATA DE CADA CAMPO QUE REQUERIMOS
        const id = $(this).data('id');
        const name = $(this).data('name');
        const code = $(this).data('code');
        const address = $(this).data('address');
        const phone = $(this).data('phone');
        const email = $(this).data('email');
        const manager_user_id  = $(this).data('manager_user_id');
        const is_active = $(this).data('is_active');        

        //PONEMOS EL VALOR DE LA DATA EN EL FORMULARIO
        $('#branchForm').attr('data-id', id);
        $('#name').val(name);
        $('#code').val(code);
        $('#address').val(address);
        $('#phone').val(phone);
        $('#email').val(email);
        $('select[name="manager_user_id"]').val(manager_user_id);
        $('select[name="is_active"]').val(is_active);

        $('#branchModalLabel').html('<i class="far fa-edit"></i> Editar Sucursal');
        //ABRIMOS EL MODAL
        $('#branchModal').modal('show');

    });


    
    // LIMPIAR AL CERRAR MODAL
    $('#branchModal').on('hidden.bs.modal', function () {
        const $form = $('#branchForm');
        $form[0].reset();
        $form.removeAttr('data-id');
        $('#branchModalLabel').html('<i class="fas fa-plus"></i> Nuevo Sucursal');
        $('#error-messages').addClass('d-none').empty();

    });



    // Inicializar DataTable
    tableBranch = $('#tableBranch').DataTable({
        processing: true,
        
        serverSide: true,
        ajax: window.routes.branchesList,
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'address', name: 'address' },
            { data: 'phone', name: 'phone' },
            { data: 'email', name: 'email' },
            { data: 'is_active', name: 'is_active' },
            { data: 'acciones', name: 'acciones', orderable: false, searchable: false }
        ],
       responsive: true,
        autoWidth: false,

        // ✅ ÚNICO DOM — diseño correcto y responsivo
        dom: `
        <'row mb-3'<'col-sm-12 col-md-6 text-start'l><'col-sm-12 col-md-6 text-end'f>>
        <'row'<'col-sm-12'tr>>
        <'row mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7 d-flex justify-content-center justify-content-md-end'p>>
        <'row mt-3'<'col-sm-12 text-center'B>>
        `,

        language: {
            url: "/vendor/datatables/js/i18n/es-ES.json"
        },

        buttons: [
            { extend: 'excel', className: 'btn btn-success btn-sm', text: '<i class="fas fa-file-excel"></i> Excel' },
            { extend: 'pdf', className: 'btn btn-danger btn-sm', text: '<i class="fas fa-file-pdf"></i> PDF' },
            { extend: 'print', className: 'btn btn-secondary btn-sm', text: '<i class="fas fa-print"></i> Imprimir' }
        ],

        // Opcional: efecto visual mientras carga
        preDrawCallback: function () {
            divLoading && divLoading.classList.remove('d-none');
        },
        drawCallback: function () {
            divLoading && divLoading.classList.add('d-none');
            // Activar tooltips de Bootstrap en cada render
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
        }
    });

    // Eliminar Sucursal
    $(document).on('click', '.deleteBranch', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            
            if (result.isConfirmed) {
                $.ajax({
                    url: `${window.routes.deleteBranch}/${id}`,
                    type: 'DELETE',
                    success: function (response) {
                        tableBranch.ajax.reload(null, false);
                        Swal.fire({
                            icon: 'success',
                            title: response.message,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    },
                    error: function () {
                        Swal.fire('Error', 'Ocurrió un error al eliminar el Tipo', 'error');
                    }
                });
            }
        });
    });



});
