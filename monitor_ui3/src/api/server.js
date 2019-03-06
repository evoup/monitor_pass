import request from '@/utils/request'

export function add_server(name, agent_addr, jmx_addr, snmp_addr) {
  return request({
    url: '/server/info',
    method: 'post',
    data: {
      name,
      agent_addr,
      jmx_addr,
      snmp_addr
    }
  })
}
