FROM node:18-alpine

RUN apk add --no-cache \
    chromium \
    firefox \
    wqy-zenhei \
    dumb-init

WORKDIR /app

COPY package.json ./

RUN npm install --omit=dev

COPY . .

EXPOSE 3001

ENTRYPOINT ["/usr/sbin/dumb-init", "--"]
CMD ["node", "src/index.js"]
