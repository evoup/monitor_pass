import request from '../utils/request'

export function notifaction_mode_list(param) {
  return request({
    url: '/notification_mode_config/list',
    method: 'get',
    params: param
  })
}
