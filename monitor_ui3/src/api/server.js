import request from '@/utils/request'

export function add_server(name, agent_addr, jmx_addr, snmp_addr, idc) {
  return request({
    url: '/server/info',
    method: 'post',
    data: {
      name,
      agent_addr,
      jmx_addr,
      snmp_addr,
      idc
    }
  })
}

export function server_list(param) {
  return request({
    url: '/server/list',
    method: 'get',
    params: param
  })
}

export function server_group_list(param) {
  return request({
    url: 'server_group/list',
    method: 'get',
    param: param
  })
}
