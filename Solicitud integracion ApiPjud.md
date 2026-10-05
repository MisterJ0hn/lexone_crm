Necesito integrar con sistema de api-pjud. Para esto estoy exponiendo endpoints en https://api-pjud.codifica.cl

para inciar debemos utilizar el endpoint:
- /auth/login
curl -X 'POST' \
  'https://api-pjud.codifica.cl/auth/login' \
  -H 'accept: */*' \
  -H 'x-client-key: 3453d' \
  -H 'Content-Type: application/json' \
  -d '{
  "email": "user@example.com",
  "password": "string"
}'
Los parametros x-client-key, email, password debe ser configurable desde empresa.

con esto necesito que habilitemos un popup al igual que en estado diario, 
te dejo la ruta de los popups D:\sitios\temposoft\estado_diario\frontend\src\app\features\causas\components

este popup debemos habilitarlo en estado diario de este crm con un boton igual que el detalle pjud de estado diario. adjunto la ruta donde esta eso: D:\sitios\temposoft\estado_diario\frontend\src\app\features\causas\causas.component.ts
crealo como un diseño global, este popup lo utilizaremos en otros modulos distintos del estado diario.
El diseño debe ser lo mas parecido que el de estado diario.
