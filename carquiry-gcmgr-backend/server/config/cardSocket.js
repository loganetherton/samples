/**
 * Socket.io configuration
 */

/**
 * Connect to socket for updating cards
 * @param socketio
 */
function socketConnect(socketio) {
  socketio.on('connection', function (socket) {
    socket.address = socket.handshake.address !== null ?
                     socket.handshake.address.address + ':' + socket.handshake.address.port :
                     process.env.DOMAIN;

    socket.connectedAt = new Date();

    // Call onConnect.
    require('../api/card/card.socket').register(socket);
  });
}

export default socketConnect;
