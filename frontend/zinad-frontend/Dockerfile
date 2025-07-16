FROM node:18-alpine AS build

WORKDIR /app

COPY frontend/zinad-frontend/package.json frontend/zinad-frontend/package-lock.json ./

RUN --mount=type=cache,target=/root/.npm npm ci

COPY frontend/zinad-frontend/ .

RUN npm run build

FROM nginx:alpine

COPY --chown=nginx:nginx --from=build /app/dist/zinad-frontend/browser /usr/share/nginx/html

COPY docker/nginx/frontend.conf /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
