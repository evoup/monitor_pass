import request from '@/utils/request'

export function add_server(name, agent_addr, ssh_addr, jmx_addr, snmp_addr, data_collector, idc, server_groups,
  templates, auto_asset, monitoring) {
  return request({
    url: '/server/info',
    method: 'post',
    data: {
      name,
      agent_addr,
      ssh_addr,
      jmx_addr,
      snmp_addr,
      data_collector,
      idc,
      server_groups,
      templates,
      auto_asset,
      monitoring
    }
  })
}

export function change_server(id, name, agent_addr, ssh_addr, jmx_addr, snmp_addr, data_collector, idc, server_groups,
  templates, auto_asset, monitoring) {
  return request({
    url: '/server/info',
    method: 'put',
    data: {
      id,
      name,
      agent_addr,
      ssh_addr,
      jmx_addr,
      snmp_addr,
      data_collector,
      idc,
      server_groups,
      templates,
      auto_asset,
      monitoring
    }
  })
}

export function read_server(param) {
  return request({
    url: '/server/info',
    method: 'get',
    params: param
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
    url: '/server_group/list',
    method: 'get',
    params: param
  })
}

export function add_server_group(name, desc, alarm_type, user_groups, templates) {
  return request({
    url: '/server_group/info',
    method: 'post',
    data: {
      name,
      desc,
      alarm_type,
      user_groups,
      templates
    }
  })
}

export function delete_server_group(param) {
  return request({
    url: '/server_group/info',
    method: 'delete',
    params: param
  })
}
