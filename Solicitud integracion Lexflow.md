Necesito integrar con sistema de estado diario. Para esto estoy exponiendo endpoints en https://edapi.temposoft.cl/docs

endpoints estado diario:
- /api/v1/estado-diario/no-leidos
- /api/v1/estado-diario/leidos
- /api/v1/estado-diario/pendientes
endpoint movimientos:
- /api/v1/movimientos
endpoint audiencias:
- /api/v1/audiencias

Autentificacion:
curl https://<servidor>/api/v1/causas \
  -H "X-API-Key: ed_xxxxxxxxxxxxxxxx"

- utiliza el header X-API-Key, se debe configurar.

Primero necesito un modulo que muestre el estado diario.
la pantalla mostrará 2 tabs [Materias,Cortes].
tabs Materias:
    debe mostrar los estados diarios obtenidos desde edapi. y debe mostrar las mismas columnas que estado diario. ver en D:\sitios\temposoft\estado_diario\frontend\src\app\features\estado-diario\components\movimientos-list\movimientos-list.component.ts
tabs cortes:
    ver columnas en D:\sitios\temposoft\estado_diario\frontend\src\app\features\estado-diario\components\cortes-list\cortes-list.component.ts
imita las busquedas al igual que en sistema estado diario

luego pasemos a movimientos.
la pantalla tambien mostrará 2 tabs [Materias, Cortes].
tabs Materias:  
    ver igual a D:\sitios\temposoft\estado_diario\frontend\src\app\features\movimientos\movimientos.component.ts
tabs Cortes:
    ver igual a D:\sitios\temposoft\estado_diario\frontend\src\app\features\movimientos\movimientos-cortes.component.ts

como final pasamos a audiencias.
será solo una pantalla que debe verse igual a D:\sitios\temposoft\estado_diario\frontend\src\app\features\audiencias\audiencias.component.ts


