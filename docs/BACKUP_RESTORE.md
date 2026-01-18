# Guía de Restauración de Backups - Grin Web

Esta guía explica cómo restaurar un backup del sistema almacenado en Cloudflare R2.

---

## Prerrequisitos

1. Acceso al panel de administración para descargar el backup
2. El archivo `.zip` del backup descargado
3. Acceso al servidor (Plesk o SSH)

---

## Método 1: Restauración con phpMyAdmin (Plesk)

### Paso 1: Descargar el Backup
1. Ir a **Sistema → Copias de Seguridad** en el panel admin
2. Clic en el icono de descarga (⬇️) del backup deseado
3. Guardar el archivo `.zip` en tu computadora

### Paso 2: Extraer el Backup
1. Descomprimir el archivo `.zip` en tu computadora
2. Dentro encontrarás:
   - `db-dumps/mysql-grintic.sql` (base de datos)
   - `storage/app/...` (archivos subidos)

### Paso 3: Restaurar Base de Datos en Plesk
1. Acceder a **Plesk → Bases de datos → phpMyAdmin**
2. Seleccionar la base de datos del proyecto
3. Ir a la pestaña **Importar**
4. Clic en **Seleccionar archivo** → elegir `mysql-grintic.sql`
5. Clic en **Importar**
6. Esperar a que termine (puede tardar según el tamaño)

### Paso 4: Restaurar Archivos (opcional)
1. Ir a **Plesk → Administrador de archivos**
2. Navegar a `/httpdocs/storage/app/`
3. Subir los archivos extraídos de `storage/app/`

### Paso 5: Limpiar Caché
1. Ir a **Plesk → Terminal** o acceder por SSH
2. Ejecutar:
```bash
cd ~/httpdocs
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

---

## Método 2: Restauración por Línea de Comandos (SSH)

### Paso 1: Subir el Backup al Servidor
```bash
scp backup-2026-01-17.zip usuario@servidor:~/
```

### Paso 2: Conectar al Servidor
```bash
ssh usuario@servidor
cd ~/httpdocs  # o la ruta de tu proyecto
```

### Paso 3: Poner en Mantenimiento
```bash
php artisan down --secret="acceso-emergencia-123"
```
> Podrás acceder al sitio con: `tudominio.com/acceso-emergencia-123`

### Paso 4: Extraer el Backup
```bash
mkdir -p ~/restore-temp
unzip ~/backup-2026-01-17.zip -d ~/restore-temp
```

### Paso 5: Restaurar Base de Datos
```bash
mysql -u usuario_db -p nombre_base_datos < ~/restore-temp/db-dumps/mysql-grintic.sql
```
> Ingresa la contraseña de la base de datos cuando se solicite

### Paso 6: Restaurar Archivos de Storage
```bash
cp -r ~/restore-temp/storage/app/* storage/app/
chown -R www-data:www-data storage/app/
chmod -R 775 storage/app/
```

### Paso 7: Limpiar Caché y Levantar
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan up
```

### Paso 8: Limpieza
```bash
rm -rf ~/restore-temp
rm ~/backup-2026-01-17.zip
```

---

## Notas Importantes

| ⚠️ Advertencia | Descripción |
|----------------|-------------|
| **Antes de restaurar** | Haz un backup de los datos actuales primero |
| **Migraciones** | Si hay migraciones nuevas, ejecuta `php artisan migrate` después |
| **Archivos .env** | El backup NO incluye `.env`, asegúrate de mantenerlo |
| **Permisos** | Verifica que `storage/` tenga permisos 775 |

---

## Contacto de Emergencia

Si tienes problemas durante la restauración, contacta al equipo de desarrollo.
