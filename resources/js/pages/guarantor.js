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

    // ======================================================
    // GUARDAR / ACTUALIZAR GARANTE (ANTI DOBLE CLICK)
    // ======================================================
    let isSubmittingGuarantor = false;


    $("#guarantorForm").on("submit", function (e) {

        e.preventDefault();

        // 🚫 Evitar doble envío
        if (isSubmittingGuarantor) {
            return;
        }

        // Validación rápida tipo documento
        if (!$('#guarantor_document_type').val()) {

            $('#guarantor_document_type').addClass('is-invalid');

            Swal.fire({
                icon: 'warning',
                title: 'Tipo de documento requerido',
                text: 'Debe seleccionar el tipo de documento.',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3500
            });

            return;
        }

        isSubmittingGuarantor = true;

        const $form = $(this);
        const $btn = $('#btnSaveGuarantor');
        const id = $form.attr("data-id");

        // 🔒 Bloquear botón inmediatamente
        $btn.prop('disabled', true);
        $btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Guardando...');

        divLoading.style.display = "flex";

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

        $.ajax({
            url: url,
            type: method,
            data: formData,
            processData: false,
            contentType: false,

            success: function (response) {

                resetGuarantorSubmitState();

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

                resetGuarantorSubmitState();

                if (xhr.status === 422) {

                    const errors = xhr.responseJSON.errors || {};

                    $form.find(".is-invalid").removeClass("is-invalid");
                    $form.find(".invalid-feedback").text("");

                    $.each(errors, function (field, messages) {

                        let input = $("#guarantor_" + field);
                        if (!input.length) {
                            input = $("#" + field);
                        }

                        input.addClass("is-invalid");
                        $("#" + field + "-error").text(messages[0]);
                    });

                } else {

                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: xhr.responseJSON?.message ?? "Error al guardar el garante.",
                        toast: true,
                        position: "top-end",
                        timer: 3500
                    });
                }
            }
        });
    });


    function resetGuarantorSubmitState() {

        isSubmittingGuarantor = false;

        const $btn = $('#btnSaveGuarantor');

        $btn.prop('disabled', false);
        $btn.html('<i class="fas fa-save mr-1"></i> Guardar Garante');

        divLoading.style.display = "none";
    }
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
    // ======================================================
    //  MODAL VER GARANTE
    // ======================================================
    $(document).on('click', '.viewGuarantor', function () {

        const full_name = $(this).data('full_name');
        const document_type = $(this).data('document_type');
        const document_number = $(this).data('document_number');
        const status = $(this).data('status');
        const phone = $(this).data('phone');
        const alt_phone = $(this).data('alt_phone');
        const email = $(this).data('email');
        const address = $(this).data('address');
        const company_name = $(this).data('company_name');
        const ruc = $(this).data('ruc');
        const relationship = $(this).data('relationship');
        const occupation = $(this).data('occupation');
        const is_external = $(this).data('is_external');
        const photoUrl = $(this).data('photo');
        const createdBy = $(this).data('created_by');
        const createdAt = $(this).data('created_at');
        const id = $(this).data('id');

        const defaultAvatar =
            'https://www.shutterstock.com/image-vector/default-avatar-profile-icon-social-600nw-1906669723.jpg';

        // FOTO
        $('#vg_photo').attr(
            'src',
            photoUrl || defaultAvatar
        );

        // Nombre y documento
        $('#vg_full_name').text(full_name || '—');
        $('#vg_document').text(
            (document_type || '') + ' · ' + (document_number || '—')
        );

        // Estado
        if (status === 1 || status === true || status === '1') {
            $('#vg_status')
                .removeClass()
                .addClass('badge badge-success py-2 px-3')
                .text('Activo');
        } else {
            $('#vg_status')
                .removeClass()
                .addClass('badge badge-danger py-2 px-3')
                .text('Inactivo');
        }

        // Contacto
        $('#vg_phone').text(phone || '—');
        $('#vg_alt_phone').text(alt_phone || '—');
        $('#vg_email').text(email || '—');
        $('#vg_address').text(address || '—');

        // Empresa / Persona
        $('#vg_company').text(company_name || '—');
        $('#vg_ruc').text(ruc || '—');

        // Relación y ocupación
        $('#vg_relationship').text(relationship || '—');
        $('#vg_occupation').text(occupation || '—');

        // Tipo de garante
        $('#vg_type').text(is_external ? 'Externo' : 'Cliente registrado');

        // Info adicional
        $('#vg_id').text(id || '—');
        $('#vg_created_by').text(createdBy || '—');
        $('#vg_created_at').text(createdAt || '—');

        // Mostrar modal
        $('#viewGuarantorModal').modal('show');
    });

    // ============================
    // BUSCAR DNI / RUC (GARANTE)
    // ============================

    const $gDocType = $('#guarantor_document_type');
    const $gDocNumber = $('#guarantor_document_number');

    function buscarDocumentoGarante() {

        const docType = $gDocType.val();
        const numero = $gDocNumber.val().trim();

        if (!numero || !/^\d+$/.test(numero)) return;

        // Validaciones
        if (docType === 'DNI' && numero.length === 11) {
            Swal.fire({
                icon: 'info',
                title: 'Parece un RUC',
                text: 'Selecciona RUC como tipo de documento',
                toast: true,
                position: 'top-end',
                timer: 3000,
                showConfirmButton: false
            });
            return;
        }

        if (docType === 'RUC' && numero.length === 8) {
            Swal.fire({
                icon: 'info',
                title: 'Parece un DNI',
                text: 'Selecciona DNI como tipo de documento',
                toast: true,
                position: 'top-end',
                timer: 3000,
                showConfirmButton: false
            });
            return;
        }

        // 🚨 Validar que haya seleccionado tipo de documento
        if (!docType) {

            $gDocType.addClass('is-invalid');

            Swal.fire({
                icon: 'warning',
                title: 'Tipo de documento requerido',
                text: 'Debe seleccionar el tipo de documento antes de ingresar el número.',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3500,
                timerProgressBar: true
            });

            $gDocType.focus();
            return;
        }

        $gDocNumber.prop('disabled', true);

        const url = `/admin/guarantors/consultar/${numero}`;

        $.ajax({
            url: url,
            type: 'GET',
            success: function (response) {

                $gDocNumber.prop('disabled', false);

                console.log(response);

                // 🚨 Si RENIEC devuelve mensaje de DNI no válido
                if (response.data && response.data.message) {

                    Swal.fire({
                        icon: 'info',
                        title: 'Documento no encontrado',
                        text: 'No se encontraron datos para el DNI ingresado.',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3500,
                        timerProgressBar: true
                    });

                    $('#guarantor_first_name').val('');
                    $('#guarantor_last_name').val('');
                    $('#guarantor_company_name').val('');
                    $('#guarantor_ruc').val('');

                    return;
                }

                // ✅ Si sí hay datos reales
                if (response.type === 'DNI') {

                    const p = response.data;

                    $('#guarantor_first_name').val(p.nombres || '');
                    $('#guarantor_last_name').val(
                        ((p.apellidoPaterno || '') + ' ' + (p.apellidoMaterno || '')).trim()
                    );

                    $('#guarantor_phone').focus();
                }

                if (response.type === 'RUC') {

                    const e = response.data;

                    $('#guarantor_company_name').val(e.razonSocial || '');
                    $('#guarantor_ruc').val($gDocNumber.val());

                    $('#guarantor_document_type').val('RUC');

                    $('#guarantor_phone').focus();
                }
            },
            error: function (xhr) {

                $gDocNumber.prop('disabled', false);

                let message = 'Ocurrió un error al consultar el documento.';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: message,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3500
                });
            }
        });
    }

    // ENTER
    $gDocNumber.on('keyup', function (e) {
        if (e.key === 'Enter') {
            buscarDocumentoGarante();
        }
    });

    // BLUR
    $gDocNumber.on('blur', function () {
        buscarDocumentoGarante();
    });

    $gDocType.on('change', function () {
        if ($(this).val()) {
            $(this).removeClass('is-invalid');
            $('#document_type-error').text('');
        }
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
