# 1. Propósito y Dominio
contexto:
  proyecto: "Portal de gestión de proyectos y facturación para empresa de Software y Cloud."
  modulos: ["Clientes", "Proyectos", "Etapas", "Tareas", "Subtareas", "Archivos", "Facturación", "Pagos", "Suscripciones", "Cotizaciones"]
  objetivo: "Flujo profesional: Subtareas -> Tareas -> Etapas -> Proyecto -> Facturación."

# 2. Identidad Visual y UX de Alto Rendimiento (Fewer Clicks)
estilo_visual:
  tema: "Light Mode (Fondo #F9FAFB)"
  paleta:
    primario: "#0082c9" # Azul Nextcloud
    secundario: "#001529" # Azul Night
  estetica: "Diseño minimalista, bordes rounded-xl, sombras sutiles, Tailwind CSS 4.0."
  idioma_ui: "Español"
  ux_prioridad:
    - "Accesos rápidos: Command Palette (Cmd+K) para navegación global."
    - "Cero recarga: Partial Reloads de Inertia y Laravel Reverb para datos 'Live'."
    - "Acciones rápidas: Modales y Sliders laterales para creación/edición sin cambiar de página."

# 3. Arquitectura Técnica
arquitectura:
  stack: "Laravel 12, Vue 3.5+ (Composition API), Inertia.js 2.0, TypeScript."
  seguridad:
    - "Uso de UUIDs para todos los modelos (incluyendo la tabla Users)."
    - "Sistema de roles y permisos (Spatie Laravel Permission)."
  datos:
    - "Precisión monetaria: BCMath en lógica y Decimal(15,2) en base de datos."
    - "Archivos: Spatie MediaLibrary."
    - "Estados: Uso obligatorio de PHP 8.4 Enums en 'App/Enums'."

# 4. Lógica de Negocio y Automatización
logica_automatizada:
  jerarquia_estados: 
    - "Una Tarea se completa cuando todas sus Subtareas están completas."
    - "Una Etapa se completa cuando todas sus Tareas están completas."
    - "Progreso: Calculado mediante la columna 'weight' (peso) de las tareas para mayor precisión."
  facturacion:
    - "Las facturas deben permitir pagos parciales."
    - "Estado 'Pagada' solo cuando SUM(payments) >= total_factura."

# 5. Instrucciones Críticas para el Agente (IA)
instrucciones_agente:
  backend:
    - "Crear un FormRequest por cada operación de escritura."
    - "Lógica compleja en App/Actions."
    - "Registrar cambios en ActivityLog."
  frontend:
    - "Componentes Vue con TypeScript estricto y Optimistic UI."
  calidad:
    - "Generar un Test de Pest (Unitario) para cada Action de facturación."

# 6. Estructura de Clientes y Seguridad
clientes_b2b:
  modelo: "Relación 1:N entre Company y User (Contactos)."
  acceso: "Los Contactos heredan acceso a los proyectos de su Empresa."
  interfaz: "Checkbox para 'Generar y enviar contraseña aleatoria' al crear contactos."

# 7. Funcionalidades Tech-Pro
funcionalidades_extra:
  - "Plantillas: Clonación de estructuras predefinidas de proyectos."
  - "Filtros y Buscador: Implementar en tiempo real con autocompletado global."

# 8. Localización y Contabilidad Global
localizacion_fiscal:
  moneda_global: "USD"
  impuestos: "0% (No aplicar IVA/VAT)."
  precision_db: "decimal(15,2) para amount, price y total en todas las tablas financieras."
  paises_soportados:
    Colombia: "NIT o RUT"
    Argentina: "CUIT"
    Mexico: "RFC"
    Costa_Rica: "Cédula Jurídica"
    USA: "EIN"
    Espana: "NIF o CIF"

# 9. Seguridad y Soporte Técnico
seguridad_avanzada:
  - "2FA opcional para administradores."
  - "Backups automáticos en S3 y Soft Deletes en datos críticos."

# 10. Comunicación
notificaciones:
  - "Email automático al cliente para facturas/proposals."
  - "Notificación interna 'Live' para comentarios y archivos."

# 11. Facturación Recurrente y Servicios
facturacion_recurrente:
  tipos_servicio: ["Hosting", "Servidores", "CDN", "Suscripciones", "Mantenimiento"]
  logica: "Precios pactados por cliente. Generar proforma 5 días antes del ciclo."

# 12. Cotizaciones (Proposals)
cotizaciones:
  flujo: "Cotización -> Botón 'Aceptar' -> Generación automática de Proyecto y Factura."
  estados: ["Borrador", "Enviada", "Aceptada", "Rechazada"]

# 13. Portal del Cliente (Permisos Estrictos)
portal_cliente:
  permisos: ["Ver progreso", "Descargar facturas", "Comentar", "Subir archivos"]
  restricciones: "Prohibido crear o editar estructuras de proyectos o estados financieros."