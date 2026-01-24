<!-- Modal para información de venta -->
<div class="modal fade" id="saleInfoModal" tabindex="-1" aria-labelledby="saleInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles de la Venta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Información del Cliente -->
                    <div class="col-12"><h6 class="text-primary border-bottom pb-2 mb-3">Información del Cliente</h6></div>
                    <div class="col-md-6 mb-2"><strong>Nombre:</strong> <span id="modal-nombre"></span></div>
                    <div class="col-md-6 mb-2"><strong>Apellido:</strong> <span id="modal-apellido"></span></div>
                    <div class="col-md-6 mb-2"><strong>Email:</strong> <span id="modal-email"></span></div>
                    <div class="col-md-6 mb-2"><strong>Teléfono:</strong> <span id="modal-telefono"></span></div>
                    <div class="col-md-6 mb-2"><strong>ID Personal:</strong> <span id="modal-identificacion"></span></div>
                    <div class="col-md-6 mb-2"><strong>Fuente:</strong> <span id="modal-fuente"></span></div>

                    <!-- Información de Pago -->
                    <div class="col-12 mt-3"><h6 class="text-primary border-bottom pb-2 mb-3">Información de Pago</h6></div>
                    <div class="col-md-6 mb-2"><strong>Método de Pago:</strong> <span id="modal-metodo-pago"></span></div>
                    <div class="col-md-6 mb-2"><strong>Formato de Pago:</strong> <span id="modal-tipo-acuerdo"></span></div>
                    <div class="col-md-6 mb-2"><strong>Tipo de Contrato:</strong> <span id="modal-tipo-contrato"></span></div>
                    <div class="col-md-6 mb-2"><strong>Fecha de Cierre:</strong> <span id="modal-fecha-cierre"></span></div>

                    <!-- Información del Contrato -->
                    <div class="col-12 mt-3"><h6 class="text-primary border-bottom pb-2 mb-3">Información del Contrato</h6></div>
                    <div class="col-md-6 mb-2"><strong>Contrato:</strong> <span id="modal-contrato"></span></div>
                    <div class="col-md-6 mb-2"><strong>Estado del Contrato:</strong> <span id="modal-contrato-estado"></span></div>
                    <div class="col-12 mb-2"><strong>Forma de Pago (Contrato):</strong> <span id="modal-forma-pago"></span></div>
                    <div class="col-md-6 mb-2"><strong>Fecha de Firma:</strong> <span id="modal-fecha-firma"></span></div>

                    <!-- Información Financiera -->
                    <div class="col-12 mt-3"><h6 class="text-primary border-bottom pb-2 mb-3">Información Financiera</h6></div>
                    <div class="col-md-4 mb-2"><strong>Monto Total:</strong> <span id="modal-monto-total"></span></div>
                    <div class="col-md-4 mb-2"><strong>Descuento:</strong> <span id="modal-descuento"></span></div>
                    <div class="col-md-4 mb-2"><strong>Monto Final:</strong> <span id="modal-monto-final"></span></div>
                    <div class="col-md-6 mb-2"><strong>Estado de Pago:</strong> <span id="modal-estado-pago"></span></div>
                    <div class="col-md-6 mb-2"><strong>Total Pagado:</strong> <span id="modal-total-pagado"></span></div>

                    <!-- Estado de Cuotas -->
                    <div class="col-12 mt-3"><h6 class="text-primary border-bottom pb-2 mb-3">Estado de Cuotas</h6></div>
                    <div class="col-12 mb-3" id="modal-cuotas-container">
                        <!-- Las cuotas se llenarán dinámicamente -->
                    </div>

                    <!-- Comentarios -->
                    <div class="col-12 mt-3"><h6 class="text-primary border-bottom pb-2 mb-3">Comentarios/Aclaraciones</h6></div>
                    <div class="col-12 mb-3"><span id="modal-comentarios"></span></div>

                    <!-- Acciones - Comprobantes -->
                    <div class="col-12 mt-3"><h6 class="text-primary border-bottom pb-2 mb-3">Comprobantes de Pago</h6></div>
                    <div class="col-12" id="modal-comprobantes-container">
                        <!-- Los comprobantes se llenarán dinámicamente -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
