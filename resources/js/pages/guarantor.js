var divLoading = document.getElementById('divLoading');
let tableGuarantor;

document.addEventListener("DOMContentLoaded", function () {
    //csrf token para AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // ============================
    //   DATATABLE GARANTES
    // ============================
    tableGuarantor = $('#tableGuarantor').DataTable({
        processing: true,
        serverSide: true,
        ajax: window.routes.guarantorList,
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'id', name: 'id' },

            { data: 'full_name', name: 'full_name' },
            { data: 'document_number', name: 'document_number' },
            { data: 'document_type', name: 'document_type' },

            { data: 'phone', name: 'phone' },
            { data: 'email', name: 'email' },

            { data: 'status', name: 'status' },

            { data: 'acciones', name: 'acciones', orderable: false, searchable: false }
        ],
        responsive: true,
        autoWidth: false,
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
        preDrawCallback: function () {
            divLoading && divLoading.classList.remove('d-none');
        },
        drawCallback: function () {
            divLoading && divLoading.classList.add('d-none');

            // Activar tooltips nuevamente
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
        }
    });


    // ============================
    //   EDITAR GARANTE
    // ============================
    $(document).on("click", ".editGuarantor", function () {

        let guarantorId = $(this).data("id");

        $.ajax({
            url: `/admin/guarantors/${guarantorId}/edit`,
            type: "GET",
            success: function (response) {
                if (response.status !== "success") {
                    Swal.fire("Error", "No se pudo cargar el garante.", "error");
                    return;
                }

                const g = response.data;
                const $form = $("#guarantorForm");

                $form[0].reset();
                $form.attr("data-id", g.id); // modo EDIT
                $form.find(".is-invalid").removeClass("is-invalid");
                $form.find(".invalid-feedback").text("");

                // ==============================
                //   COINCIDIR IDS DEL MODAL
                //   (todos llevan prefix guarantor_)
                // ==============================

                // Relación opcional con cliente
                $('#guarantor_client_id').val(g.client_id);

                // Switch externo / cliente
                $('#is_external').prop('checked', !!g.is_external);

                // Estado
                $('#guarantor_status').val(g.status);

                // Documento
                $('#guarantor_document_type').val(g.document_type);
                $('#guarantor_document_number').val(g.document_number);

                // Persona natural
                $('#guarantor_first_name').val(g.first_name);
                $('#guarantor_last_name').val(g.last_name);

                // Empresa
                $('#guarantor_company_name').val(g.company_name);
                $('#guarantor_ruc').val(g.ruc);

                // Contacto
                $('#guarantor_phone').val(g.phone);
                $('#guarantor_alt_phone').val(g.alt_phone);
                $('#guarantor_email').val(g.email);
                $('#guarantor_address').val(g.address);

                // Info adicional
                $('#guarantor_relationship').val(g.relationship);
                $('#guarantor_occupation').val(g.occupation);

                // Foto
                if (g.photo) {
                    $("#guarantor_img_preview").attr("src", `/storage/${g.photo}`);
                } else {
                    $("#guarantor_img_preview").attr(
                        "src",
                        "https://www.shutterstock.com/image-vector/default-avatar-profile-icon-social-600nw-1906669723.jpg"
                    );
                }

                // Título e ícono
                $("#guarantorModalLabel").text("Editar Garante");
                $(".icon_modal_guarantor").html('<i class="fas fa-user-edit text-primary"></i>');

                $("#guarantorModal").modal("show");
            },
            error: function () {
                Swal.fire("Error", "No se pudo cargar el garante.", "error");
            }
        });

    });


    // ============================
    //   GUARDAR / ACTUALIZAR GARANTE
    // ============================
    $("#guarantorForm").on("submit", function (e) {
        e.preventDefault();

        const $form = $(this);
        const id = $form.attr("data-id");

        let url = "";
        let method = "";

        const formData = new FormData(this);

        if (id) {
            url = `/admin/guarantors/${id}`;
            method = "POST";
            formData.append("_method", "PUT");
        } else {
            url = "/admin/guarantors";
            method = "POST";
        }

        divLoading.style.display = "flex";

        $.ajax({
            url: url,
            type: method,
            data: formData,
            processData: false,
            contentType: false,

            success: function (response) {
                divLoading.style.display = "none";
                $("#guarantorModal").modal("hide");

                tableGuarantor.ajax.reload(null, false);

                Swal.fire({
                    icon: "success",
                    title: response.message,
                    toast: true,
                    position: "top-end",
                    timer: 3000,
                    showConfirmButton: false
                });
            },

            error: function (xhr) {
                divLoading.style.display = "none";

                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors || {};

                    $form.find(".is-invalid").removeClass("is-invalid");
                    $form.find(".invalid-feedback").text("");

                    $.each(errors, function (field, messages) {
                        // Para inputs: usamos prefix guarantor_
                        // Ej: document_number -> #guarantor_document_number
                        let input = $("#guarantor_" + field);
                        if (!input.length) {
                            // fallback por si algún campo no sigue el patrón
                            input = $("#" + field);
                        }

                        input.addClass("is-invalid");

                        // Los spans de error están con id: field-error (document_number-error, etc.)
                        $("#" + field + "-error").text(messages[0]);
                    });

                } else {
                    Swal.fire("Error", "Error al guardar el garante.", "error");
                }
            }
        });
    });

    // LIMPIAR AL CERRAR EL MODAL DE GARANTE
    $('#guarantorModal').on('hidden.bs.modal', function () {
        const $form = $('#guarantorForm');

        // Limpia valores del formulario
        $form[0].reset();
        $form.removeAttr('data-id');

        // Título por defecto
        $('#guarantorModalLabel').text('Nuevo Garante');

        // Icono por defecto
        $('.icon_modal_guarantor').html('<i class="fas fa-user-shield text-secondary"></i>');

        // Imagen por defecto
        $('#guarantor_img_preview').attr(
            'src',
            'https://www.shutterstock.com/image-vector/default-avatar-profile-icon-social-600nw-1906669723.jpg'
        );

        // 🔥 LIMPIA VALIDACIONES DE CADA INPUT
        $form.find('.is-invalid').removeClass('is-invalid'); // quita borde rojo
        $form.find('.invalid-feedback').text('');            // limpia mensajes, pero NO elimina los spans
    });

    // ============================
    //   ELIMINAR GARANTE
    // ============================
    $(document).on("click", ".deleteGuarantor", function () {
        const guarantorId = $(this).data("id");

        Swal.fire({
            title: "¿Eliminar garante?",
            text: "Esta acción no se puede deshacer.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar",
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: `/admin/guarantors/${guarantorId}`,
                type: "DELETE",
                success: function (response) {
                    tableGuarantor.ajax.reload(null, false);

                    Swal.fire({
                        icon: "success",
                        title: response.message,
                        toast: true,
                        position: "top-end",
                        timer: 3000,
                        showConfirmButton: false,
                    });
                },
                error: function () {
                    Swal.fire("Error", "No se pudo eliminar el garante.", "error");
                }
            });
        });
    });


    // ============================
    //   PREVISUALIZAR IMAGEN
    // ============================
    window.previewGuarantorImage = function (event) {
        let file = event.target.files[0];
        if (!file) return;

        let imgUrl = URL.createObjectURL(file);
        $("#guarantor_img_preview").attr("src", imgUrl);
    };
});
