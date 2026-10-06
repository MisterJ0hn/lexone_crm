/**
 * Popup global "Detalle PJUD" (réplica del de Estado Diario).
 *
 * Uso desde cualquier módulo: un botón con data-pjud-causa="<id de Causa del CRM>"
 * (ver templates/pjud/_boton.html.twig). El popup consulta /pjud/causa/{id}, que a su vez
 * consulta en vivo api-pjud.codifica.cl, y se refresca solo mientras el PJUD sincroniza.
 *
 * Materias: civil, familia, laboral, cobranza y penal. Cada una se describe en MATERIAS
 * (panel de datos, documentos y pestañas con sus columnas).
 */
(function () {
    'use strict';

    var POLL_MS = 5000;
    var ctx = document.getElementById('pjud-popup');
    if (!ctx) { return; }
    var URL_DETALLE = ctx.dataset.urlDetalle;      // .../pjud/causa/__ID__
    var URL_DOC = ctx.dataset.urlDocumento;        // .../pjud/documento
    var URL_PERFIL = ctx.dataset.urlPerfil;        // .../mis_datos/
    var CSRF = ctx.dataset.csrf;

    var el = {
        overlay: ctx.querySelector('.pjud-overlay'),
        titulo: ctx.querySelector('.pjud-titulo'),
        subtitulo: ctx.querySelector('.pjud-subtitulo'),
        cuerpo: ctx.querySelector('.pjud-cuerpo'),
        sub: ctx.querySelector('.pjud-sub-overlay'),
        subTitulo: ctx.querySelector('.pjud-sub-titulo'),
        subCuerpo: ctx.querySelector('.pjud-sub-cuerpo'),
    };

    var estado = null; // { causaId, datos, tab, cuaderno, verAnexos, verReceptor, cargando, error, timer, sesion }

    // ───────────────────────── utilidades ─────────────────────────

    function esc(v) {
        return String(v === null || v === undefined ? '' : v)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }
    function val(v) { return (v === null || v === undefined || v === '') ? '-' : esc(v); }
    function lista(v) { return Array.isArray(v) ? v : []; }

    var ICONO_PDF = '<svg viewBox="0 0 1920 1920" fill="currentColor" width="20" height="20" aria-hidden="true"><g fill-rule="evenodd">' +
        '<path d="M1251.654 0c44.499 0 88.207 18.07 119.718 49.581l329.223 329.224c31.963 31.962 49.581 74.54 49.581 119.717V1920H169V0Zm-66.183 112.941H281.94V1807.06h1355.294V564.706H1185.47V112.94Zm112.94 23.379v315.445h315.445L1298.412 136.32Z"/>' +
        '<path d="M900.497 677.67c26.767 0 50.372 12.65 67.991 37.835 41.901 59.068 38.965 121.976 23.492 206.682-5.308 29.14.113 58.617 16.263 83.125 22.814 34.786 55.68 82.673 87.981 123.219 23.718 29.93 60.198 45.854 97.13 40.885 23.718-3.276 52.292-5.986 81.656-5.986 131.012 0 121.186 46.757 133.045 89.675 6.55 25.976 3.275 48.678-10.165 65.506-16.715 22.701-51.162 34.447-101.534 34.447-55.793 0-74.202-9.487-122.767-24.96-27.445-8.81-55.906-10.617-83.69-3.275-55.453 14.456-146.936 36.48-223.284 46.983-40.772 5.647-77.816 26.654-102.438 60.875-55.454 76.8-106.842 148.518-188.273 148.518-21.007 0-40.32-7.567-56.244-22.701-23.492-23.492-33.544-49.581-28.574-79.85 13.778-92.95 128.075-144.79 196.066-182.625 16.037-8.923 28.687-22.589 36.592-39.53l107.86-233.223c7.68-16.377 10.051-34.56 7.228-52.518-12.537-79.059-31.06-211.99 18.748-272.075 10.955-13.44 26.09-21.007 42.917-21.007Zm20.556 339.953c-43.257 126.607-119.718 264.282-129.996 280.32 92.273-43.37 275.916-65.28 275.916-65.28-92.386-88.998-145.92-215.04-145.92-215.04Z"/></g></svg>';
    var ICONO_CARPETA = '<svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20" aria-hidden="true"><path d="M3 6a2 2 0 0 1 2-2h3.5l2 2H19a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6Z"/></svg>';
    var ICONO_GLOBO = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="20" height="20" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M3 12h18M12 3c2.5 2.7 4 6.2 4 9s-1.5 6.3-4 9c-2.5-2.7-4-6.2-4-9s1.5-6.3 4-9Z"/></svg>';

    function esCertificado(url, tipo) { return tipo === 'certificado' || /_doc2(\.|$)/i.test(url || ''); }

    /** Ícono que abre un PDF del PJUD (a través de nuestro proxy) en otra pestaña. */
    function pdf(url, tipo, color) {
        if (!url) { return '<span>-</span>'; }
        var cert = esCertificado(url, tipo);
        var estilo = color ? ' style="color:' + esc(color) + '"' : '';
        return '<a class="pjud-pdf ' + (cert ? 'pjud-pdf-cert' : '') + '" target="_blank" rel="noopener"' + estilo +
            ' href="' + esc(URL_DOC + '?url=' + encodeURIComponent(url)) + '" title="' + (cert ? 'Ver certificado' : 'Ver documento') + ' (PDF)">' + ICONO_PDF + '</a>';
    }
    function pdfs(docs) {
        docs = lista(docs);
        if (!docs.length) { return '<span>-</span>'; }
        return '<span class="pjud-pdfs">' + docs.map(function (d, i) { return pdf(d.url, i === 1 ? 'certificado' : d.tipo, d.color); }).join('') + '</span>';
    }
    function badge(v) { return v ? '<span class="badge badge-secondary">' + esc(v) + '</span>' : '<span>-</span>'; }
    function carpeta(n, accion, idx, titulo) {
        return '<button type="button" class="pjud-link" data-pjud-accion="' + accion + '" data-idx="' + idx + '" title="' + esc(titulo) + '">' +
            ICONO_CARPETA + '<span class="pjud-n">' + n + '</span></button>';
    }

    // ───────────────────────── definición de columnas ─────────────────────────
    // Una columna es [titulo, (fila, indice) => html, clase opcional]

    function t(campo, clase) { return [null, function (r) { return val(r[campo]); }, clase || '']; }
    function col(titulo, campo, clase) { var c = t(campo, clase); c[0] = titulo; return c; }
    function colHtml(titulo, fn, clase) { return [titulo, fn, clase || '']; }

    var cDocs = colHtml('Doc.', function (r) { return pdfs(r.documentos); }, 'c');
    var cPdf = function (titulo, campo) { return colHtml(titulo, function (r) { return pdf(r[campo]); }, 'c'); };
    var cAnexo = function (titulo, seccion) {
        return colHtml(titulo, function (r, i) {
            var a = lista(r.anexo);
            return a.length ? carpeta(a.length, 'anexos-tramite', seccion + ':' + i, 'Ver anexos') : '<span>-</span>';
        }, 'c');
    };
    var cGeo = function (titulo, campo) {
        return colHtml(titulo, function (r, i) {
            return r[campo] ? '<button type="button" class="pjud-link pjud-geo" data-pjud-accion="geo" data-idx="' + i + '" title="Ver georeferencia">' + ICONO_GLOBO + '</button>' : '<span>-</span>';
        }, 'c');
    };
    var cFolio = colHtml('Folio', function (r) { return val(r.folio_texto !== undefined && r.folio_texto !== null ? r.folio_texto : r.folio); }, 'c');

    var MATERIAS = {
        civil: {
            titulo: 'Detalle Causa Civil',
            panel: function (c, d) {
                var cu = cuadernoActual(c);
                return [
                    ['ROL', c.rol || d.crm.rol], ['F. Ing.', c.fecha_ingreso], ['', c.caratula],
                    ['Est. Adm.', c.est_adm], ['Proc.', c.proceso], ['Ubicación', c.ubicacion],
                    ['Estado Proc.', (cu && cu.estado_proceso) || c.estado_proceso], ['Etapa', (cu && cu.etapa) || c.etapa], ['Tribunal', c.tribunal || d.crm.tribunal],
                ].concat(c.causa_origen && (c.causa_origen.rol || c.causa_origen.tribunal)
                    ? [['Causa Origen', c.causa_origen.rol], ['Tribunal Origen', c.causa_origen.tribunal]] : []);
            },
            docs: function (c) { return docsSimples(c, [['Texto Demanda', 'texto_demanda'], ['Certificado de Envío', 'certificado_envio'], ['Ebook', 'ebook']]); },
            anexosCausa: { titulo: 'Anexos de la causa', campo: 'anexos_causa', cols: [cPdf('Doc.', 'doc'), col('Fecha', 'fecha'), colHtml('Referencia', function (a) { return val(a.referencia || a.nombre_doc); })] },
            cuadernos: true, receptor: true,
            tabs: [
                { k: 'historia', t: 'Historia', vacio: 'El PJUD no registra trámites en este cuaderno.', cols: [
                    cFolio, cDocs, cAnexo('Anexo', 'historia'), col('Etapa', 'etapa'), col('Trámite', 'tramite'), col('Desc. Trámite', 'descripcion_tramite'),
                    col('Fec. Trámite', 'fecha_tramite'), col('Foja', 'foja', 'c'), cGeo('Georref.', 'georeferencia')] },
                { k: 'litigantes', t: 'Litigantes', vacio: 'Sin litigantes registrados.', cols: [col('Participante', 'participante'), col('Rut', 'rut'), col('Persona', 'persona'), col('Nombre o Razón Social', 'razon_social')] },
                { k: 'notificaciones', t: 'Notificaciones', vacio: 'Sin notificaciones registradas.', cols: [
                    col('ROL', 'rol'), colHtml('Est. Notif.', function (r) { return badge(r.estado_notificacion); }), col('Tipo Notif.', 'tipo_notificacion'), col('Fecha Trámite', 'fecha_tramite'),
                    col('Tipo Part.', 'tipo_part'), col('Nombre', 'nombre'), col('Trámite', 'tramite'), col('Obs. Fallida', 'observacion_fallida')] },
                { k: 'escritos_resolver', t: 'Escritos por Resolver', vacio: 'No hay escritos pendientes de resolución.', cols: [
                    cPdf('Doc.', 'doc'), cAnexo('Anexo', 'escritos_resolver'), col('Fecha de Ingreso', 'fecha_ingreso'), col('Tipo Escrito', 'tipo_escrito'), col('Solicitante', 'solicitante')] },
                { k: 'exhortos', t: 'Exhortos', vacio: 'Sin exhortos registrados.', cols: [
                    col('Rol Origen', 'rol_origen'), colHtml('Tipo Exhorto', function (r) { return val(r.tipo_exhorto || 'Exhorto'); }),
                    colHtml('Rol Destino', function (r, i) {
                        var rd = lista(r.rol_destino);
                        return rd.length ? '<button type="button" class="pjud-link" data-pjud-accion="rol-destino" data-idx="' + i + '">' + esc(rd.map(function (x) { return x.nombre; }).filter(Boolean).join(', ') || 'Rol destino') + '</button>' : '<span>-</span>';
                    }, 'c'),
                    col('Fecha Ordena Exhorto', 'fecha_ordena_exhorto'), col('Fecha Ingreso Exhorto', 'fecha_ingreso_exhorto'), col('Tribunal Destino', 'tribunal_destino'),
                    colHtml('Estado Exhorto', function (r) { return badge(r.estado_exhorto); })] },
                { k: 'piezas_exhorto', t: 'Piezas Exhorto', soloSiHay: true, cols: [
                    col('Folio', 'folio', 'c'), cPdf('Doc.', 'doc'), col('Cuaderno', 'cuaderno', 'c'),
                    colHtml('Anexo', function (r) { return lista(r.anexo).length ? '<span class="pjud-link">' + ICONO_CARPETA + '<span class="pjud-n">' + r.anexo.length + '</span></span>' : '<span>-</span>'; }, 'c'),
                    col('Etapa', 'etapa'), col('Trámite', 'tramite'), col('Desc. Trámite', 'descripcion_tramite'), col('Fec. Trámite', 'fecha_tramite'), col('Foja', 'foja', 'c')] },
            ],
        },

        familia: {
            titulo: 'Detalle Causa Familia',
            panel: function (c, d) {
                return [['ROL', c.rit || d.crm.rol],  ['', c.caratula],['F. Ing.', c.fecha_ingreso], ['RUC', c.ruc], ['Proc.', c.proceso], ['Forma Inicio', c.forma_inicio],
                    ['Est. Adm.', c.est_adm], ['Etapa', c.etapa], ['Estado Proc.', c.estado_proceso], ['Tribunal', c.tribunal || d.crm.tribunal, 'ancho']];
            },
             anexosCausa: { titulo: 'Anexos de la causa', campo: 'anexos_causa', cols: [col('Folio', 'folio', 'c'), cPdf('Doc.', 'doc'), col('Fecha', 'fecha'), col('Referencia', 'referencia')] },
            
            docs: function (c) { return docsSimples(c, [['Ebook', 'ebook'],['Certificado de Envío', 'certificado_envio'] ]); },
            tabs: [
                { k: 'movimientos', t: 'Movimiento', vacio: 'El PJUD no registra movimientos.', cols: [
                    cFolio, cDocs, cAnexo('Anexos', 'movimientos'), col('Etapa', 'etapa'), col('Estado', 'estado'), col('Trámite', 'tramite'), col('Desc. Trámite', 'descripcion_tramite'),
                    col('Fecha Trámite', 'fecha_tramite'), cGeo('Georreferencia', 'georeferencia')] },
                { k: 'litigantes', t: 'Litigantes', vacio: 'Sin litigantes registrados.', cols: [col('Sujeto', 'sujeto'), col('Rut', 'rut'), col('Persona', 'persona'), col('Nombre o Razón Social', 'razon_social')] },
                { k: 'notificaciones', t: 'Notificaciones', vacio: 'Sin notificaciones registradas.', cols: [
                    col('Estado y Fecha Notif.', 'estado_fecha_notif'), col('Tipo Notif.', 'tipo_notif'), col('Ente Notif.', 'ente_notif'), col('RIT', 'rit'), col('RUC', 'ruc'),
                    col('Fecha Trámite', 'fecha_tramite'), col('Tipo Parte', 'tipo_parte'), col('Nombre', 'nombre'), col('Trámite', 'tramite'), col('Certificación', 'certificacion')] },
                { k: 'materias', t: 'Materias', vacio: 'Sin materias registradas.', cols: [col('Código', 'codigo'), col('Glosa de materia', 'glosa_de_materia'), col('Estado', 'estado'), col('Fecha Término', 'fecha_termino')] },
                { k: 'plazos', t: 'Plazos', vacio: 'Sin plazos registrados.', cols: [
                    col('Tipo Plazo', 'tipo_plazo'), col('Ámbito Afectado', 'ambito_afectado'), col('Fecha Inicio', 'fecha_inicio'), col('Fecha Término', 'fecha_termino'), col('Duración', 'duracion'),
                    col('Estado', 'estado'), col('Trámite', 'tramite'), col('Fecha Suspensión', 'fecha_suspension'), col('Fecha Reactivación', 'fecha_reactivacion')] },
                { k: 'diligencias', t: 'Diligencias', vacio: 'Sin diligencias registradas.', cols: [
                    cPdf('Doc. Solicitud', 'doc_solicitud'), cPdf('Doc. Respuesta', 'doc_respuesta'), col('Estado Diligencia', 'estado_diligencia'), col('Tipo Diligencia', 'tipo_diligencia'), col('Fecha Trámite', 'fecha_tramite')] },
            ],
        },

        laboral: {
            titulo: 'Detalle Causa Laboral',
            panel: function (c, d) {
                return [['RIT', c.rit || d.crm.rol], ['F. Ing.', c.fecha_ingreso], ['RUC', c.ruc], ['Proc.', c.proceso], ['Forma Inicio', c.forma_inicio], ['', c.caratula, 'der'],
                    ['Est. Adm.', c.est_adm], ['Etapa', c.etapa], ['Estado Proc.', c.estado_proceso], ['Tribunal', c.tribunal || d.crm.tribunal, 'ancho'], ['Trámites', c.tramites, 'ancho']];
            },
            docs: function (c) { return docsSimples(c, [['Ebook', 'ebook'], ['Certificado de Envío', 'certificado_envio']]); },
            tablasCausa: [
                { titulo: 'Texto Demanda', campo: 'texto_demanda', cols: [col('Doc. Demanda', 'doc_demanda', 'c'), cPdf('Doc.', 'doc'), col('Fecha', 'fecha'), col('Referencia', 'referencia')] },
                { titulo: 'Audio Laboral', campo: 'audio_laboral', cols: [
                    col('N°', 'numero', 'c'), cPdf('Descargar', 'audio'),
                    colHtml('Audio', function (a) { return a.audio ? '<audio controls preload="none" src="' + esc(URL_DOC + '?url=' + encodeURIComponent(a.audio)) + '"></audio>' : '<span>-</span>'; }),
                    col('Fecha', 'fecha'), col('Referencia', 'referencia')] },
            ],
            tabs: [
                { k: 'movimiento', t: 'Movimiento', vacio: 'El PJUD no registra movimientos.', cols: [
                    cFolio, cDocs, cAnexo('Anexos', 'movimiento'), col('Etapa', 'etapa'), col('Trámite', 'tramite'), col('Desc. Trámite', 'descripcion_tramite'),
                    col('Fecha Trámite', 'fecha_tramite'), col('Estado', 'estado'), cGeo('Georref.', 'georeferencia')] },
                { k: 'litigantes', t: 'Litigantes', vacio: 'Sin litigantes registrados.', cols: [
                    col('Est.', 'estado', 'c'), col('Abog. Defensor', 'defensor'), col('Sujeto', 'sujeto'), col('Rut', 'rut'), col('Persona', 'persona'), col('Nombre o Razón Social', 'razon_social')] },
                { k: 'notificaciones', t: 'Notificaciones', vacio: 'Sin notificaciones registradas.', cols: [
                    col('Estado Notif.', 'estado_notificacion'), col('Fecha Trámite', 'fecha_tramite'), col('Tipo Parte', 'tipo_part'), col('Nombre', 'nombre'), col('Trámite', 'tramite'), col('Obs. Fallida', 'observacion_fallida')] },
                { k: 'diligencias', t: 'Diligencias', vacio: 'Sin diligencias registradas.', cols: [
                    cPdf('Doc. Ida', 'doc_ida'), cPdf('Doc. Vta', 'doc_vta'), col('Estado Diligencia', 'estado_diligencia'), col('RIT', 'rit'), col('RUC', 'ruc'),
                    col('Tipo Diligencia', 'tipo_diligencia'), col('Referencia', 'referencia'), col('Fecha Trámite', 'fecha_tramite')] },
                { k: 'liquidacion', t: 'Liquidación', vacio: 'Sin liquidaciones registradas.', cols: [col('Liquidación', 'liquidacion'), col('Rut', 'rut'), col('Nombre', 'nombre'), col('Monto Líquido', 'monto_liquido')] },
                { k: 'materias', t: 'Materias', vacio: 'Sin materias registradas.', cols: [col('Código', 'codigo'), col('Glosa de Materia', 'glosa_materia'), col('Estado', 'estado'), col('Fecha Término', 'fecha_termino')] },
                { k: 'escritos_pendientes', t: 'Escritos Pendientes', vacio: 'Sin escritos pendientes.', cols: [
                    cPdf('Doc.', 'doc'), col('Anexo', 'anexo'), col('Fecha Ing.', 'fecha_ing'), col('Referencia', 'referencia'), col('Solicitante', 'solicitante'), col('Tipo Ingreso', 'tipo_ingreso')] },
            ],
        },

        cobranza: {
            titulo: 'Detalle Causa Cobranza',
            panel: function (c, d) {
                var cu = cuadernoActual(c);
                return [['RIT', c.rit || d.crm.rol], ['Fecha Ing.', c.fecha_ingreso], ['RUC', c.ruc], ['Est.Adm.', c.est_adm], ['Proc.', c.proceso], ['Forma Inicio', c.forma_inicio],
                    ['Estado Proc.', (cu && cu.estado_proceso) || c.estado_proceso], ['Etapa', (cu && cu.etapa) || c.etapa], ['Título Ejec.', null, '', pdf(c.titulo_ejec && c.titulo_ejec.url)],
                    ['Juez Asignado', c.juez_asignado], ['Tribunal', c.tribunal || d.crm.tribunal]];
            },
            docs: function (c) { return docsSimples(c, [['Doc. Demanda', 'doc_demanda'], ['Ebook', 'ebook'], ['Certificado de Envío', 'certificado_envio']]); },
            anexosCausa: { titulo: 'Anexos de la causa', campo: 'anexos_causa', cols: [cPdf('Doc.', 'doc'), col('Fecha', 'fecha'), col('Referencia', 'referencia')] },
            tablasCausa: [{ titulo: 'Documentos Laboral', campo: 'documentos_laboral', cols: [cPdf('Doc.', 'doc'), col('Fecha', 'fecha'), col('Referencia', 'referencia')] }],
            cuadernos: true, receptor: true,
            tabs: [
                { k: 'historia', t: 'Historia', vacio: 'El PJUD no registra trámites en este cuaderno.', cols: [
                    cFolio, cDocs, cAnexo('Anexo', 'historia'), col('Etapa', 'etapa'), col('Trámite', 'tramite'),
                    colHtml('Desc. Trámite', function (r) {
                        return r.descripcion_tramite_doc ? '<a target="_blank" rel="noopener" href="' + esc(URL_DOC + '?url=' + encodeURIComponent(r.descripcion_tramite_doc)) + '">' + val(r.descripcion_tramite) + '</a>' : val(r.descripcion_tramite);
                    }),
                    col('Estado Firma', 'estado_firma'), col('Fec. Trámite', 'fecha_tramite'), cGeo('Georref.', 'georeferencia')] },
                { k: 'litigantes', t: 'Litigantes', vacio: 'Sin litigantes registrados.', cols: [col('Sujeto', 'sujeto'), col('Rut', 'rut'), col('Persona', 'persona'), col('Nombre o Razón Social', 'razon_social')] },
                { k: 'notificaciones', t: 'Notificaciones', vacio: 'Sin notificaciones registradas.', cols: [
                    col('Tip.Not.', 'tipo_notificacion'), col('Est.Not.', 'estado_notificacion'), col('Fec.Not.', 'fecha_notificacion'), col('Fec.Tram.', 'fecha_tramite'),
                    col('Trámite', 'tramite'), col('Tip.Part.', 'tipo_part'), col('Nombre', 'nombre')] },
                { k: 'diligencias', t: 'Diligencias', vacio: 'Sin diligencias registradas.', cols: [
                    cPdf('Doc. Ida', 'doc_ida'), cPdf('Doc. Vta', 'doc_vta'), col('Estado Diligencia', 'estado_diligencia'), col('RIT', 'rit'), col('RUC', 'ruc'),
                    col('Tipo Diligencia', 'tipo_diligencia'), col('Fecha Trámite', 'fecha_tramite'), col('Destinatario', 'destinatario'), col('Responsable', 'responsable')] },
                { k: 'liquidacion', t: 'Liquidación', vacio: 'Sin liquidaciones registradas.', cols: [
                    cPdf('Liquidación', 'liquidacion'), col('Fecha Liquidación', 'fecha_liquidacion'), col('Cuaderno', 'cuaderno'), col('Estado', 'estado'), col('Monto Líquido', 'monto_liquido')] },
            ],
        },

        penal: {
            titulo: 'Detalle Causa Penal',
            panel: function (c, d) {
                var cu = cuadernoActual(c);
                return [['ROL / RUC', (c.rol || d.crm.rol) + ' / ' + (c.ruc || '-')], ['Fecha Ingreso', c.fecha_ingreso], ['Caratulado', c.caratula],
                    ['Est.Adm.', c.estado_adm], ['Procedimiento', c.procedimiento], ['Ubicación', c.ubicacion],
                    ['Estado Procesal', (cu && cu.estado_proceso) || c.estado_proceso], ['Etapa', (cu && cu.etapa) || c.etapa], ['Tribunal', c.tribunal || d.crm.tribunal]];
            },
            docs: function (c) {
                var out = [];
                if (c.acumulada) { out.push(['Acumulada', pdf(c.acumulada)]); }
                if (c.certificado_envio) { out.push(['Certificado de Envío', pdf(c.certificado_envio)]); }
                return out;
            },
            cuadernos: true,
            tabs: [
                { k: 'historia', t: 'Historia', vacio: 'El PJUD no registra trámites en este cuaderno.', cols: [
                    cFolio, cDocs, cAnexo('Anexo', 'historia'), col('Trámite', 'tramite'), col('Desc. Trámite', 'descripcion_tramite'), col('Fec. Trámite', 'fecha_tramite'),
                    col('Fec. Firma', 'fecha_firma'), col('Estado', 'estado')] },
                { k: 'litigantes', t: 'Litigantes', vacio: 'Sin litigantes registrados.', cols: [col('Participantes', 'participantes'), col('Persona', 'persona'), col('Nombre o Razón Social', 'razon_social')] },
                { k: 'notificaciones', t: 'Notificaciones', vacio: 'Sin notificaciones registradas.', cols: [
                    col('Tipo Notificación', 'tipo_notificacion'), col('Estado Notificación', 'estado_notificacion'), col('Fecha Notificación', 'fecha_notificacion'), col('Nombre', 'nombre'),
                    col('Estampado', 'estampado'), cGeo('Geo', 'geo')] },
                { k: 'relaciones', t: 'Relaciones', vacio: 'Sin relaciones registradas.', cols: [col('Nombre', 'nombre'), col('Materia', 'materia'), col('Estado Causa', 'estado_causa'), col('Fecha Cambio Estado', 'fecha_cambio_estado')] },
            ],
        },
    };

    /** Documentos de la cabecera que son un {url} (o varios), como filas [etiqueta, html]. */
    function docsSimples(c, defs) {
        var out = [];
        defs.forEach(function (d) {
            var v = c[d[1]];
            if (v && v.url) { out.push([d[0], pdf(v.url)]); }
        });
        return out;
    }

    function cuadernoActual(c) {
        var cuadernos = lista(c && c.cuadernos);
        if (!cuadernos.length) { return null; }
        var id = estado && estado.datos ? estado.datos.cuaderno_consultado_id : null;
        for (var i = 0; i < cuadernos.length; i++) { if (cuadernos[i].id === id) { return cuadernos[i]; } }
        return cuadernos[0];
    }

    // ───────────────────────── render ─────────────────────────

    function tabla(cols, filas, vacio) {
        if (!filas.length) { return '<p class="text-muted small">' + esc(vacio || 'Sin registros.') + '</p>'; }
        var h = '<div class="pjud-tabla-wrap"><table class="pjud-table"><thead><tr>' +
            cols.map(function (c) { return '<th>' + esc(c[0]) + '</th>'; }).join('') + '</tr></thead><tbody>';
        filas.forEach(function (f, i) {
            h += '<tr>' + cols.map(function (c) { return '<td class="' + c[2] + '">' + c[1](f, i) + '</td>'; }).join('') + '</tr>';
        });
        return h + '</tbody></table></div>';
    }

    function spinner(g) {
        return '<svg class="pjud-spin" width="' + (g || 20) + '" height="' + (g || 20) + '" viewBox="0 0 24 24" fill="none"><circle opacity=".25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path opacity=".75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>';
    }

    function render() {
        var s = estado;
        if (!s) { return; }
        var d = s.datos;
        var h = '';

        if (s.cargando && !d) {
            el.cuerpo.innerHTML = '<div class="pjud-centro">' + spinner(34) + '</div>';
            return;
        }
        if (s.error) {
            el.cuerpo.innerHTML = '<div class="alert alert-danger mb-0">' + esc(s.error) + '</div>';
            return;
        }
        if (!d) { return; }

        var cfg = MATERIAS[d.materia];
        if (cfg) { el.titulo.textContent = cfg.titulo; }
        el.subtitulo.textContent = d.crm.rol + ' — ' + d.crm.tribunal;

        if (d.estado === 'sincronizando') {
            h += '<div class="alert alert-info"><strong>El Poder Judicial está sincronizando esta causa</strong><br>' +
                esc(d.mensaje || 'Puede tardar varios minutos.') + ' Esta ventana se actualiza sola en cuanto el Poder Judicial responda.' +
                (d.detalle_estado ? '<div class="pjud-chip">' + spinner(16) + ' ' + esc(d.detalle_estado) + '</div>' : '') + '</div>';
        }
        if (d.estado === 'error') {
            h += '<div class="pjud-error"><strong>La sincronización con el Poder Judicial falló</strong>' +
                '<div>' + esc(d.ultimo_error || d.detalle_estado || d.mensaje || '') + '</div>' +
                '<button type="button" class="btn btn-light btn-sm mt-2" data-pjud-accion="actualizar"' + (s.cargando ? ' disabled' : '') + '>' + (s.cargando ? 'Reintentando...' : 'Reintentar') + '</button></div>';
        }
        if (d.estado === 'sin_credenciales') {
            h += '<div class="alert alert-warning"><strong>Falta tu clave del Poder Judicial</strong><br>' + esc(d.mensaje || '') +
                '<br><a class="btn btn-primary btn-sm mt-2" href="' + esc(URL_PERFIL) + '">Ir a Mis Datos</a></div>';
        }

        var c = d.causa;
        if ((d.estado === 'listo' || d.estado === 'sincronizando') && c && cfg) {
            h += panel(cfg, c, d) + controles(cfg, c, d) + pestanas(cfg, d);
        }
        el.cuerpo.innerHTML = h;
    }

    function panel(cfg, c, d) {
        var h = '<div class="pjud-panel"><div class="pjud-grid">';
        cfg.panel(c, d).forEach(function (f) {
            var contenido = f[3] !== undefined ? f[3] : val(f[1]);
            var cls = f[2] === 'der' ? 'pjud-der' : (f[2] === 'ancho' ? 'pjud-ancho' : '');
            h += '<p class="' + cls + '">' + (f[0] ? '<span class="pjud-k">' + esc(f[0]) + ':</span> ' : '') + contenido + '</p>';
        });
        h += '</div>';

        var docs = cfg.docs ? cfg.docs(c) : [];
        var anexos = cfg.anexosCausa && lista(c[cfg.anexosCausa.campo]).length ? cfg.anexosCausa : null;
        var extra = (cfg.tablasCausa || []).filter(function (x) { return lista(c[x.campo]).length; });
        if (docs.length || anexos || extra.length) {
            h += '<div class="pjud-docs">';
            docs.forEach(function (x) { h += '<span class="pjud-doc"><span class="pjud-k">' + esc(x[0]) + ':</span> ' + x[1] + '</span>'; });
            if (anexos) {
                h += '<span class="pjud-doc"><span class="pjud-k">' + esc(anexos.titulo) + ':</span> ' + carpeta(lista(c[anexos.campo]).length, 'toggle-anexos', 'x', 'Ver anexos de la causa') + '</span>';
            }
            extra.forEach(function (x, i) {
                h += '<span class="pjud-doc"><span class="pjud-k">' + esc(x.titulo) + ':</span> ' + carpeta(lista(c[x.campo]).length, 'toggle-extra', i, 'Ver ' + x.titulo) + '</span>';
            });
            h += '</div>';
            if (anexos && estado.verAnexos) { h += '<div class="pjud-desplegable">' + tabla(anexos.cols, lista(c[anexos.campo])) + '</div>'; }
            extra.forEach(function (x, i) {
                if (estado.verExtra === i) { h += '<div class="pjud-desplegable">' + tabla(x.cols, lista(c[x.campo])) + '</div>'; }
            });
        }
        return h + '</div>';
    }

    function controles(cfg, c, d) {
        var h = '';
        var cuadernos = lista(c.cuadernos);
        var recep = cfg.receptor ? lista(c.informacion_receptor) : [];
        if ((cfg.cuadernos && cuadernos.length) || recep.length) {
            h += '<div class="pjud-controles">';
            if (cfg.cuadernos && cuadernos.length) {
                h += '<div><label class="pjud-k d-block mb-1">Historia Causa Cuaderno</label><select class="form-control form-control-sm" data-pjud-accion="cuaderno"' + (cuadernos.length === 1 ? ' disabled' : '') + '>';
                cuadernos.forEach(function (cu) {
                    h += '<option value="' + esc(cu.id) + '"' + (cu.id === d.cuaderno_consultado_id ? ' selected' : '') + '>' + esc(cu.nombre) + '</option>';
                });
                h += '</select></div>';
            }
            if (recep.length) {
                h += '<div><span class="pjud-k d-block mb-1">Información notificaciones receptor</span>' + carpeta(recep.length, 'toggle-receptor', 'r', 'Ver información del receptor') + '</div>';
            }
            h += '</div>';
            if (recep.length && estado.verReceptor) {
                h += tabla([col('Cuaderno', 'cuaderno'), col('Datos de retiro', 'datos_retiro'), col('Fecha retiro', 'fecha_retiro'), col('Estado', 'estado')], recep);
            }
        }
        return h;
    }

    function pestanas(cfg, d) {
        var sec = d.secciones || {};
        var tabs = cfg.tabs.filter(function (tb) { return !tb.soloSiHay || lista(sec[tb.k]).length > 0; });
        if (!tabs.some(function (tb) { return tb.k === estado.tab; })) { estado.tab = tabs[0].k; }

        var h = '';
        var pend = d.materia === 'civil' ? lista(sec.escritos_resolver).length : 0;
        if (pend) { h += '<div class="alert alert-warning">' + pend + ' escrito(s) por resolver — ver la pestaña.</div>'; }

        h += '<ul class="nav nav-tabs pjud-tabs">';
        tabs.forEach(function (tb) {
            h += '<li class="nav-item"><a href="#" class="nav-link ' + (tb.k === estado.tab ? 'active' : '') + '" data-pjud-accion="tab" data-idx="' + tb.k + '">' +
                esc(tb.t) + ' <span class="badge badge-light pjud-contador">' + lista(sec[tb.k]).length + '</span></a></li>';
        });
        h += '</ul><div class="pt-2">';
        var activa = tabs.filter(function (tb) { return tb.k === estado.tab; })[0];
        var vacio = activa.vacio;
        if (d.estado === 'sincronizando' && activa.k === tabs[0].k) { vacio = 'El Poder Judicial todavía no entrega los trámites de este cuaderno.'; }
        h += tabla(activa.cols, lista(sec[activa.k]), vacio) + '</div>';
        return h;
    }

    // ───────────────────────── sub-popups ─────────────────────────

    function abrirSub(titulo, html) {
        el.subTitulo.textContent = titulo;
        el.subCuerpo.innerHTML = html;
        el.sub.style.display = 'flex';
    }
    function cerrarSub() { el.sub.style.display = 'none'; el.subCuerpo.innerHTML = ''; }

    // Familia trae Folio, Documento y Observación en sus anexos; las demás materias solo Doc., Fecha y Referencia.
    var COLS_ANEXO_FAMILIA = [
        colHtml('Folio', function (a) { return a.folio !== undefined ? val(a.folio) : '-'; }, 'c'),
        cPdf('Doc.', 'doc'), col('Fecha', 'fecha'),
        colHtml('Documento', function (a) { return val(a.nombre_documento || a.referencia); }),
        colHtml('Observación', function (a) { return val(a.observacion); }),
    ];
    var COLS_ANEXO = [cPdf('Doc.', 'doc'), col('Fecha', 'fecha'), col('Referencia', 'referencia')];

    function filaDe(ref) {
        var p = ref.split(':'), sec = estado.datos.secciones[p[0]];
        return sec ? sec[parseInt(p[1], 10)] : null;
    }

    function abrirGeo(g) {
        var m = g.mapa || {};
        var h = '';
        if (m.latitud && m.longitud) {
            var q = encodeURIComponent(m.latitud + ',' + m.longitud);
            h += '<div class="pjud-mapa"><iframe loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="https://maps.google.com/maps?q=' + q + '&z=17&output=embed"></iframe></div>' +
                '<p class="small text-muted"><span class="pjud-k">Latitud:</span> ' + esc(m.latitud) + ' <span class="pjud-k">Longitud:</span> ' + esc(m.longitud) +
                (m.corrector ? ' <span class="pjud-k">Corrector:</span> ' + esc(m.corrector) : '') + '</p>';
        } else {
            h += '<p class="text-muted small">Sin coordenadas registradas.</p>';
        }
        var imgs = lista(g.imagenes);
        if (imgs.length) {
            h += '<hr><div class="pjud-imgs">' + imgs.map(function (i) { return '<img src="' + esc(i.img) + '" alt="Imagen de georeferencia">'; }).join('') + '</div>';
        }
        abrirSub('Georeferencia', h);
    }

    // ───────────────────────── datos ─────────────────────────

    function pedir(metodo, forzar) {
        var s = estado;
        if (!s) { return; }
        var sesion = s.sesion;
        clearTimeout(s.timer);
        s.cargando = true;
        render();
        
        var url = URL_DETALLE.replace('__ID__', s.causaId) + (metodo === 'POST' ? '/actualizar' : '') + (s.cuaderno ? '?cuaderno=' + encodeURIComponent(s.cuaderno) : '');
        var opciones = { method: metodo, credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } };
        if (metodo === 'POST') { opciones.headers['X-CSRF-Token'] = CSRF; }

        fetch(url, opciones).then(function (r) {
            return r.json().catch(function () { return { detail: 'Respuesta ilegible del servidor' }; }).then(function (j) { return { ok: r.ok, j: j }; });
        }).then(function (res) {
            if (!estado || estado.sesion !== sesion) { return; }
            estado.cargando = false;
            if (!res.ok) {
                estado.error = res.j.detail || 'No se pudo consultar el detalle PJUD';
                render();
                return;
            }
            estado.error = null;
            estado.datos = res.j;
            render();
            if (res.j.estado === 'sincronizando') {
                estado.timer = setTimeout(function () { pedir('GET'); }, POLL_MS);
            }
        }).catch(function () {
            if (!estado || estado.sesion !== sesion) { return; }
            estado.cargando = false;
            // Error de red: si ya había datos, se sigue intentando; si no, se avisa.
            if (estado.datos && estado.datos.estado === 'sincronizando') {
                estado.timer = setTimeout(function () { pedir('GET'); }, POLL_MS);
            } else {
                estado.error = 'No se pudo conectar con el servidor.';
                render();
            }
        });
    }

    var sesionSeq = 0;
    function abrir(causaId) {
        cerrar();
        estado = { causaId: causaId, datos: null, tab: null, cuaderno: null, verAnexos: false, verReceptor: false, verExtra: null, cargando: true, error: null, timer: null, sesion: ++sesionSeq };
        el.titulo.textContent = 'Detalle PJUD';
        el.subtitulo.textContent = '';
        el.overlay.style.display = 'flex';
        document.body.classList.add('pjud-abierto');
        pedir('GET');
    }
    function cerrar() {
        if (estado) { clearTimeout(estado.timer); }
        estado = null;
        cerrarSub();
        el.overlay.style.display = 'none';
        document.body.classList.remove('pjud-abierto');
    }

    // ───────────────────────── eventos ─────────────────────────

    document.addEventListener('click', function (e) {
        var b = e.target.closest('[data-pjud-causa]');
        if (b) { e.preventDefault(); abrir(b.getAttribute('data-pjud-causa')); return; }

        if (e.target.closest('[data-pjud-cerrar]')) { cerrar(); return; }
        if (e.target.closest('[data-pjud-cerrar-sub]')) { cerrarSub(); return; }
        if (e.target === el.overlay) { cerrar(); return; }
        if (e.target === el.sub) { cerrarSub(); return; }

        var a = e.target.closest('[data-pjud-accion]');
        if (!a || !estado || !ctx.contains(a)) { return; }
        var accion = a.getAttribute('data-pjud-accion'), idx = a.getAttribute('data-idx');
        if (accion !== 'cuaderno') { e.preventDefault(); }

        if (accion === 'tab') { estado.tab = idx; render(); }
        else if (accion === 'actualizar') { pedir('POST', true); }
        else if (accion === 'toggle-anexos') { estado.verAnexos = !estado.verAnexos; render(); }
        else if (accion === 'toggle-receptor') { estado.verReceptor = !estado.verReceptor; render(); }
        else if (accion === 'toggle-extra') { var n = parseInt(idx, 10); estado.verExtra = estado.verExtra === n ? null : n; render(); }
        else if (accion === 'anexos-tramite') {
            var f = filaDe(idx);
            if (f) { abrirSub('Anexos del trámite', tabla(estado.datos.materia === 'familia' ? COLS_ANEXO_FAMILIA : COLS_ANEXO, lista(f.anexo))); }
        } else if (accion === 'geo') {
            var tab = MATERIAS[estado.datos.materia].tabs.filter(function (tb) { return tb.k === estado.tab; })[0];
            var fila = lista(estado.datos.secciones[tab.k])[parseInt(idx, 10)];
            var g = fila && (fila.georeferencia || fila.geo);
            if (g) { abrirGeo(g); }
        } else if (accion === 'rol-destino') {
            var x = lista(estado.datos.secciones.exhortos)[parseInt(idx, 10)];
            var h = '';
            lista(x && x.rol_destino).forEach(function (rd) {
                h += '<h6 class="mt-2">' + esc(rd.nombre || 'Rol destino') + '</h6>' +
                    tabla([cPdf('Doc.', 'doc'), col('Fecha', 'fecha'), col('Referencia', 'referencia'), col('Trámite', 'tramite')], lista(rd.roles));
            });
            abrirSub('Rol destino', h || '<p class="text-muted small">Sin información.</p>');
        }
    });

    document.addEventListener('change', function (e) {
        var s = e.target.closest('[data-pjud-accion="cuaderno"]');
        if (s && estado && ctx.contains(s)) {
            estado.cuaderno = s.value;
            pedir('GET');
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape' || !estado) { return; }
        if (el.sub.style.display === 'flex') { cerrarSub(); } else { cerrar(); }
    });

    window.PjudPopup = { abrir: abrir, cerrar: cerrar };
})();
