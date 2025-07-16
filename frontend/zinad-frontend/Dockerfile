FROM node:18-alpine AS angular

WORKDIR /usr/local/app

COPY frontend/zinad-frontend/ /usr/local/app/

RUN npm install

RUN npm run build

FROM nginx:alpine

COPY --from=angular /usr/local/app/dist/zinad-frontend /usr/share/nginx/html

COPY docker/nginx/frontend.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
