

var divLoading = document.getElementById('divLoading');
let tableClient;
let contactsTable = null;
let currentClientId = null;
document.addEventListener("DOMContentLoaded", function () {
    //csrf token para AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Avatar por defecto (lo tomamos del data-attribute del <img>)
    const defaultClientAvatar =
        $('#client_img_preview').data('default-avatar') ||
        'https://www.shutterstock.com/image-vector/default-avatar-profile-icon-social-600nw-1906669723.jpg';

    // ======================================================
    // EVENTO CAMBIO IMAGEN CLIENTE
    // ======================================================
    $('#client_image').on('change', function (event) {

        const input = event.target;
        const preview = document.getElementById('client_img_preview');

        if (!input.files || !input.files[0]) {
            return;
        }

        const file = input.files[0];

        if (!file.type.startsWith('image/')) {
            Swal.fire({
                icon: 'warning',
                title: 'Archivo inválido',
                text: 'Debe seleccionar una imagen válida.',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });

            input.value = '';
            return;
        }

        const reader = new FileReader();

        reader.onload = function (e) {
            preview.src = e.target.result;
        };

        reader.readAsDataURL(file);
    });
    //GUARDAR / ACTUALIZAR 

    // ======================================================
    // GUARDAR / ACTUALIZAR CLIENTE (ANTI DOBLE CLICK)
    // ======================================================
    let isSubmittingClient = false;

    $('#clientForm').on('submit', function (e) {

        e.preventDefault();

        // 🚫 Si ya se está enviando, no permitir otro submit
        if (isSubmittingClient) {
            return;
        }

        isSubmittingClient = true;

        const $form = $(this);
        const $btn = $('#btnSaveClient');

        // 🔒 Desactivar botón inmediatamente
        $btn.prop('disabled', true);
        $btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Guardando...');

        divLoading.style.display = "flex";

        // Validar edad antes de enviar
        const fechaNacimiento = $('#birth_date').val();

        if (fechaNacimiento && !esMayorDeEdad(fechaNacimiento)) {

            divLoading.style.display = "none";
            isSubmittingClient = false;
            $btn.prop('disabled', false);
            $btn.html('<i class="fas fa-save mr-1"></i> Guardar Cliente');

            $('#birth_date').addClass('is-invalid');
            $('#birth_date-error').text('El cliente debe ser mayor de 18 años.');

            Swal.fire({
                icon: 'warning',
                title: 'Edad no permitida',
                text: 'El cliente debe tener al menos 18 años.',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3500
            });

            return;
        }

        const id = $form.attr('data-id');
        let url = '';
        let type = '';

        const formData = new FormData(this);

        if (id) {
            url = "/admin/clients/" + id;
            type = 'POST';
            formData.append('_method', 'PUT');
        } else {
            url = window.routes.storeClient;
            type = 'POST';
        }

        $.ajax({
            url: url,
            type: type,
            data: formData,
            processData: false,
            contentType: false,

            success: function (response) {

                divLoading.style.display = "none";
                isSubmittingClient = false;

                $btn.prop('disabled', false);
                $btn.html('<i class="fas fa-save mr-1"></i> Guardar Cliente');

                $('#clientModal').modal('hide');
                tableClient.ajax.reload(null, false);

                Swal.fire({
                    title: response.message,
                    icon: "success",
                    toast: true,
                    position: "top-end",
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            },

            error: function (xhr) {

                divLoading.style.display = "none";
                isSubmittingClient = false;

                $btn.prop('disabled', false);
                $btn.html('<i class="fas fa-save mr-1"></i> Guardar Cliente');

                if (xhr.status === 422) {

                    const errors = xhr.responseJSON.errors || {};

                    $('.is-invalid').removeClass('is-invalid');
                    $('.invalid-feedback').text('');

                    $.each(errors, function (key, messages) {
                        const input = $(`#${key}`);
                        input.addClass('is-invalid');
                        $(`#${key}-error`).text(messages[0]);
                    });

                } else {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message ?? 'Ocurrió un error inesperado',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3500
                    });
                }
            }
        });

    });


    //PONER LOS DATOS EN EL MODAL PARA EDITAR 
    //PONER LOS DATOS EN EL MODAL PARA EDITAR 
    $(document).on('click', '.editClient', function () {

        //PONEMOS EN UNA VARIABLE LA DATA DE CADA CAMPO QUE REQUERIMOS
        const id = $(this).data('id');
        const document_type = $(this).data('document_type');
        const document_number = $(this).data('document_number');
        const first_name = $(this).data('first_name');
        const last_name = $(this).data('last_name');
        const birth_date = $(this).data('birth_date');
        const gender = $(this).data('gender');
        const marital_status = $(this).data('marital_status');
        const occupation = $(this).data('occupation');
        const phone = $(this).data('phone');
        const email = $(this).data('email');
        const status = $(this).data('status');

        // 👇 nueva: ruta de la foto
        const photo = $(this).data('photo');

        //PONEMOS EL VALOR DE LA DATA EN EL FORMULARIO
        $('#clientForm').attr('data-id', id);
        $('#document_type').val(document_type);
        $('#document_number').val(document_number);
        $('#first_name').val(first_name);
        $('#last_name').val(last_name);
        $('#birth_date').val(birth_date);
        $('#gender').val(gender);
        $('#marital_status').val(marital_status);
        $('#occupation').val(occupation);
        $('#phone').val(phone);
        $('#email').val(email);
        $('#status').val(status);

        // 👇 nueva: mostrar la foto del cliente (o el avatar por defecto)
        if (photo) {
            $('#client_img_preview').attr('src', photo);
        } else {
            $('#client_img_preview').attr('src', defaultClientAvatar);
        }

        $('.icon_modal').html('<i class="far fa-edit text-secondary"></i> ');
        $('#clientModalLabel').html('Editar Cliente');
        $('#clientModal').modal('show');

    });





    //LIMPIAR AL CERRAR EL MODDAL 
    $('#clientModal').on('hidden.bs.modal', function () {
        const $form = $('#clientForm');

        // Limpia valores del formulario
        $form[0].reset();
        $form.removeAttr('data-id');

        // Título por defecto
        $('#clientModalLabel').html('Nuevo Cliente');

        // Oculta mensajes de error generales si aún los usas
        $('#error-messages').addClass('d-none').empty();

        // 🔥 LIMPIA VALIDACIONES DE CADA INPUT
        $form.find('.is-invalid').removeClass('is-invalid');   // Borra borde rojo
        $form.find('.invalid-feedback').remove();              // Elimina los mensajes bajo cada input

        // 👇 NUEVO: volver al avatar por defecto y al ícono "nuevo"
        $('#client_img_preview').attr('src', defaultClientAvatar);
        $('.icon_modal').html('<i class="fas fa-user-plus text-secondary"></i>');
    });




    //MODAL VER CLIENTE
    $(document).on('click', '.viewClient', function () {
        // Datos básicos
        const full_name = $(this).data('full_name');
        const document_type = $(this).data('document_type');
        const document_number = $(this).data('document_number');
        const status = $(this).data('status');
        const phone = $(this).data('phone');
        const email = $(this).data('email');
        const birth_date = $(this).data('birth_date');
        const occupation = $(this).data('occupation');
        const gender = $(this).data('gender');
        const company_name = $(this).data('company_name');
        const ruc = $(this).data('ruc');
        const marital_status = $(this).data('marital_status');

        // 👇 nuevos datos
        const photoUrl = $(this).data('photo');
        const branchName = $(this).data('branch');
        const userName = $(this).data('user');
        const clientId = $(this).data('id');
        const createdAt = $(this).data('created_at');
        const creditScore = $(this).data('credit_score');

        // FOTO
        $('#vc_photo').attr(
            'src',
            photoUrl || defaultClientAvatar
        );

        // Nombre y documento
        $('#vc_full_name').text(full_name);
        $('#vc_document').text((document_type || '') + ' · ' + (document_number || '—'));

        // Estado
        if (status === 1 || status === true || status === '1') {
            $('#vc_status').removeClass().addClass('badge badge-success py-2 px-3').text('Activo');
        } else {
            $('#vc_status').removeClass().addClass('badge badge-danger py-2 px-3').text('Inactivo');
        }

        // Contacto
        $('#vc_phone').text(phone || '—');
        $('#vc_email').text(email || '—');

        // Datos personales
        $('#vc_birth_date').text(birth_date || '—');
        $('#vc_occupation').text(occupation || '—');
        $('#vc_gender').text(gender || '—');
        $('#vc_company').text(company_name || '—');
        $('#vc_ruc').text(ruc || '—');
        $('#vc_marital_status').text(marital_status || '—');

        // 👇 Sucursal y usuario
        $('#vc_branch').text(branchName || '—');
        $('#vc_user').text(userName || '—');

        // 👇 Info adicional
        $('#vc_id').text(clientId || '—');
        $('#vc_created_at').text(createdAt || '—');

        // 👇 Puntaje crediticio
        let score = parseInt(creditScore, 10);
        if (isNaN(score) || score < 0) score = 0;
        if (score > 100) score = 100;

        if (creditScore === undefined || creditScore === null || creditScore === '') {
            $('#vc_credit_score').text('—');
            $('#vc_credit_score_bar')
                .css('width', '0%')
                .attr('aria-valuenow', 0);
        } else {
            $('#vc_credit_score').text(score);
            $('#vc_credit_score_bar')
                .css('width', score + '%')
                .attr('aria-valuenow', score);
        }

        $('#viewClientModal').modal('show');
    });


    //MODAL CONTACTOS DEL CLIENTE
    // ======================================================
    //  CONTACTOS POR CLIENTE
    // ======================================================
    let contactsTable = null;
    let currentClientId = null;

    // Abrir modal de contactos al hacer clic en el botón de acciones
    $(document).on('click', '.manageContacts', function () {
        const clientId = $(this).data('id');
        const fullName = $(this).data('full_name') || 'Cliente';

        currentClientId = clientId;
        $('#contacts_client_name').text(fullName);

        // Mostrar modal
        $('#contactsModal').modal('show');

        // Inicializar o recargar DataTable de contactos
        if (contactsTable) {
            // solo recargamos y enviamos el nuevo client_id
            contactsTable.ajax.reload(null, true);
        } else {
            contactsTable = $('#contactsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: window.routes.clientContactsList,
                    type: 'GET',
                    data: function (d) {
                        d.client_id = currentClientId;
                    },
                    error: function (xhr, error, thrown) {
                        console.error('Respuesta del servidor (contactsTable):', xhr.responseText);
                    }
                },
                columns: [
                    { data: 'contact_type', name: 'contact_type' },
                    { data: 'phone', name: 'phone' },
                    { data: 'address', name: 'address' },
                    { data: 'district', name: 'district' },
                    { data: 'contact_name', name: 'contact_name' },
                    { data: 'is_primary_badge', name: 'is_primary', orderable: false, searchable: false },
                    { data: 'acciones', name: 'acciones', orderable: false, searchable: false }
                ],
                responsive: true,
                autoWidth: false,
                language: {
                    url: "/vendor/datatables/js/i18n/es-ES.json"
                }
            });

        }
    });

    // Cuando se cierra el modal de contactos, opcionalmente limpiamos estado
    $('#contactsModal').on('hidden.bs.modal', function () {
        currentClientId = null;
        // Si quieres limpiar completamente la tabla al cerrar:
        // if (contactsTable) {
        //     contactsTable.clear().draw();
        // }
    });

    // ======================================================
    //  ELIMINAR CONTACTO (botón dentro de la tabla de contactos)
    // ======================================================
    $(document).on('click', '.deleteContact', function () {
        const contactId = $(this).data('id');

        Swal.fire({
            title: '¿Eliminar contacto?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
            customClass: {
                confirmButton: 'btn btn-danger btn-sm',
                cancelButton: 'btn btn-secondary btn-sm'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${window.routes.deleteClientContact}/${contactId}`,
                    type: 'DELETE',
                    success: function (response) {
                        if (contactsTable) {
                            contactsTable.ajax.reload(null, false);
                        }
                        Swal.fire({
                            icon: 'success',
                            title: response.message || 'Contacto eliminado correctamente',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        });
                    },
                    error: function (xhr) {
                        console.error(xhr);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON && xhr.responseJSON.message
                                ? xhr.responseJSON.message
                                : 'Ocurrió un error al eliminar el contacto'
                        });
                    }
                });
            }
        });
    });

    // ======================================================
    //  BOTÓN "AGREGAR CONTACTO"
    //  (por ahora solo deja el hook listo; luego hacemos el form)
    // ======================================================
    $('#btnAddContact').on('click', function () {
        if (!currentClientId) {
            Swal.fire({
                icon: 'info',
                title: 'Sin cliente seleccionado',
                text: 'Primero selecciona un cliente para registrar contactos.'
            });
            return;
        }

        const $form = $('#contactForm');
        $form[0].reset();
        $form.removeAttr('data-id');          // SIN id => CREATE
        $('#contact_client_id').val(currentClientId); // 👈 llenamos el hidden client_id

        // Limpia errores previos
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback').text('');

        $('#contactFormModalLabel').text('Nuevo contacto');
        $('#contactFormModal').modal('show');
    });


    // FUNCION PARA GUARDAR O ACTUALIZAR CONTACTO
    // ======================================================
    // GUARDAR / ACTUALIZAR CONTACTO (ANTI DOBLE CLICK)
    // ======================================================
    let isSubmittingContact = false;

    $('#contactForm').on('submit', function (e) {

        e.preventDefault();

        // 🚫 Evitar doble submit
        if (isSubmittingContact) {
            return;
        }

        isSubmittingContact = true;

        const $form = $(this);
        const $btn = $('#contactFormModal button[type="submit"]');
        const contactId = $form.attr('data-id');

        // 🔒 Bloquear botón inmediatamente
        $btn.prop('disabled', true);
        $btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Guardando...');

        divLoading && (divLoading.style.display = "flex");

        // Asegurar client_id
        if (!$('#contact_client_id').val() && currentClientId) {
            $('#contact_client_id').val(currentClientId);
        }

        if (!$('#contact_client_id').val()) {

            resetContactSubmitState();

            Swal.fire({
                icon: 'info',
                title: 'Sin cliente',
                text: 'No se ha definido el cliente para este contacto.'
            });
            return;
        }

        let url, method;
        let dataToSend = $form.serialize();

        if (contactId) {
            url = `/admin/client-contacts/${contactId}`;
            method = 'POST';
            dataToSend += '&_method=PUT';
        } else {
            url = '/admin/client-contacts';
            method = 'POST';
        }

        $.ajax({
            url: url,
            type: method,
            data: dataToSend,

            success: function (response) {

                resetContactSubmitState();

                $('#contactFormModal').modal('hide');

                if (contactsTable) {
                    contactsTable.ajax.reload(null, false);
                }

                Swal.fire({
                    icon: 'success',
                    title: response.message || 'Contacto guardado correctamente',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            },

            error: function (xhr) {

                resetContactSubmitState();

                if (xhr.status === 422) {

                    const errors = xhr.responseJSON.errors || {};

                    $form.find('.is-invalid').removeClass('is-invalid');
                    $form.find('.invalid-feedback').text('');

                    $.each(errors, function (field, messages) {
                        const input = $form.find(`[name="${field}"]`);
                        input.addClass('is-invalid');
                        $form.find(`#${field}-error`).text(messages[0]);
                    });

                } else {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message ?? 'Ocurrió un error al guardar el contacto.'
                    });
                }
            }
        });

    });

    // 🔁 Función para restaurar estado del botón
    function resetContactSubmitState() {

        isSubmittingContact = false;

        const $btn = $('#contactFormModal button[type="submit"]');

        $btn.prop('disabled', false);
        $btn.html('<i class="fas fa-save mr-1"></i> Guardar');

        divLoading && (divLoading.style.display = "none");
    }


    // ======================================================


    //BOTON EDITAR CONTACTO
    // BOTÓN EDITAR CONTACTO
    $(document).on('click', '.editContact', function () {
        const contactId = $(this).data('id');

        $.ajax({
            url: `/admin/client-contacts/${contactId}`, // show
            type: 'GET',
            success: function (response) {
                if (response.status !== 'success') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'No se pudo cargar el contacto.'
                    });
                    return;
                }

                const contact = response.data;
                const $form = $('#contactForm');

                $form[0].reset();
                $form.attr('data-id', contact.id);        // marcar que es edición
                $('#contact_client_id').val(contact.client_id); // por si acaso

                // 👉 Usamos SIEMPRE $form.find('[name="..."]') para que solo toque el modal de contacto
                $form.find('[name="contact_type"]').val(contact.contact_type);
                $form.find('[name="address"]').val(contact.address || '');
                $form.find('[name="district"]').val(contact.district || '');
                $form.find('[name="province"]').val(contact.province || '');
                $form.find('[name="department"]').val(contact.department || '');
                $form.find('[name="reference"]').val(contact.reference || '');
                $form.find('[name="phone"]').val(contact.phone || '');
                $form.find('[name="alt_phone"]').val(contact.alt_phone || '');
                $form.find('[name="email"]').val(contact.email || '');
                $form.find('[name="contact_name"]').val(contact.contact_name || '');
                $form.find('[name="relationship"]').val(contact.relationship || '');
                $form.find('[name="is_primary"]').prop('checked', !!contact.is_primary);

                // Limpiar errores previos
                $form.find('.is-invalid').removeClass('is-invalid');
                $form.find('.invalid-feedback').text('');

                $('#contactFormModalLabel').text('Editar contacto');
                $('#contactFormModal').modal('show');
            },
            error: function (xhr) {
                console.error(xhr);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al cargar el contacto.'
                });
            }
        });
    });









    //INICIALIZAR DATATABLE
    tableClient = $('#tableClient').DataTable({
        processing: true,
        serverSide: true,
        ajax: window.routes.clientList,
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

    // Eliminar Cliente
    $(document).on('click', '.deleteClient', function () {
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
                    url: `${window.routes.deleteClient}/${id}`,
                    type: 'DELETE',
                    success: function (response) {
                        tableClient.ajax.reload(null, false);
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



    // ======================================================
    //  CONSULTA DNI / RUC (RENIEC / SUNAT)
    //  - DNI (8 dígitos) solo si tipo = DNI
    //  - RUC (11 dígitos) solo si tipo = RUC
    //  - Si se cruza (DNI con 11, RUC con 8) => mensaje
    // ======================================================
    const $documentType = $('#document_type');
    const $documentNumber = $('#document_number');

    function buscarDocumentoApi() {
        const docType = $documentType.val();          // DNI | RUC | CE | ''
        const numero = $documentNumber.val().trim(); // string

        console.log('buscarDocumentoApi()', { docType, numero, length: numero.length });

        if (!numero || !/^\d+$/.test(numero)) {
            // vacío o no solo números -> no consultamos nada
            return;
        }

        // ------- Validaciones antes de llamar a la API -------

        // Caso: selecciona DNI pero pone 11 dígitos (parece RUC)
        if (docType === 'DNI' && numero.length === 11) {
            Swal.fire({
                icon: 'info',
                title: 'Verifica el tipo de documento',
                text: 'Has ingresado 11 dígitos. Parece un RUC, selecciona la opción "RUC".',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3500,
                timerProgressBar: true
            });
            return;
        }

        // Caso: selecciona RUC pero pone 8 dígitos (parece DNI)
        if (docType === 'RUC' && numero.length === 8) {
            Swal.fire({
                icon: 'info',
                title: 'Verifica el tipo de documento',
                text: 'Has ingresado 8 dígitos. Parece un DNI, selecciona la opción "DNI".',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3500,
                timerProgressBar: true
            });
            return;
        }

        // Solo consultamos si claramente es DNI (8) o RUC (11)
        if (numero.length !== 8 && numero.length !== 11) {
            // todavía incompleto o longitud rara, no llamamos
            return;
        }

        // Si el tipo está vacío o es CE, tampoco consultamos
        // Si no ha seleccionado tipo de documento
        if (!docType) {

            $documentType.addClass('is-invalid');

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

            $documentType.focus();
            return;
        }

        // ------- Llamada a la API --------
        $documentNumber.prop('disabled', true);

        const url = window.routes.consultarDocumento.replace('DOC_PLACEHOLDER', numero);
        console.log('URL consulta:', url);

        $.ajax({
            url: url,
            type: 'GET',
            success: function (response) {
                $documentNumber.prop('disabled', false);

                console.log('Respuesta API documento:', response);

                if (!response.status || !response.data) {
                    Swal.fire({
                        icon: 'info',
                        title: 'No encontrado',
                        text: response.message || 'No se encontraron datos para el documento ingresado.',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                    return;
                }

                // response.type: 'DNI' | 'RUC'
                if (response.type === 'DNI') {
                    const persona = response.data;
                    const nombres = persona.nombres || '';
                    const apPaterno = persona.apellidoPaterno || '';
                    const apMaterno = persona.apellidoMaterno || '';

                    $('#first_name').val(nombres);
                    $('#last_name').val((apPaterno + ' ' + apMaterno).trim());

                    $('#phone').focus();
                } else if (response.type === 'RUC') {
                    const empresa = response.data;
                    const razonSocial = empresa.razonSocial || empresa.nombre || '';

                    $('#first_name').val('');
                    $('#last_name').val(razonSocial);

                    // Forzar tipo de documento a RUC
                    $documentType.val('RUC');

                    $('#phone').focus();
                }
            },
            error: function (xhr) {
                $documentNumber.prop('disabled', false);

                console.error('Error API documento:', xhr);

                let msg = 'Ocurrió un error al consultar el documento.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: msg,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            }
        });
    }

    // Enter dentro del input
    $documentNumber.on('keyup', function (e) {
        if (e.key === 'Enter') {
            buscarDocumentoApi();
        }
    });

    // Al salir del input (blur)
    $documentNumber.on('blur', function () {
        buscarDocumentoApi();
    });

    $documentType.on('change', function () {
        if ($(this).val()) {
            $(this).removeClass('is-invalid');
        }
    });

    // ======================================================
    // VALIDAR MAYORÍA DE EDAD (18+)
    // ======================================================
    function esMayorDeEdad(fechaNacimiento) {

        if (!fechaNacimiento) return true; // si está vacío no validamos aquí

        const hoy = new Date();
        const nacimiento = new Date(fechaNacimiento);

        let edad = hoy.getFullYear() - nacimiento.getFullYear();
        const mes = hoy.getMonth() - nacimiento.getMonth();

        if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
            edad--;
        }

        return edad >= 18;
    }

    // Cuando cambie la fecha
    $('#birth_date').on('change', function () {

        const fecha = $(this).val();

        if (fecha && !esMayorDeEdad(fecha)) {

            $(this).addClass('is-invalid');
            $('#birth_date-error').text('El cliente debe ser mayor de 18 años.');

            Swal.fire({
                icon: 'warning',
                title: 'Edad no permitida',
                text: 'El cliente debe tener al menos 18 años.',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3500,
                timerProgressBar: true
            });

            $(this).val('');
            $(this).focus();
        } else {
            $(this).removeClass('is-invalid');
            $('#birth_date-error').text('');
        }
    });



});
