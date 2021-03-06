// 验证名字
var validateName = (rule, value, callback) => {
  if (!value) {
    callback(new Error('名字不能为空'))
  } else if (/[a-zA-Z]/.test(value)) {
    callback(new Error('请填写中文名字！'))
  } else {
    callback()
  }
}

// 验证年龄
var validateAge = (rule, value, callback) => {
  const toNumberVal = Number(value)
  if ((typeof value === 'string' && value === '') || (value === null)) {
    callback(new Error('年龄不允许为空'))
  } else if (isNaN(toNumberVal)) {
    callback(new Error('年龄为数值类型'))
  } else if (!(toNumberVal > 0 && toNumberVal <= 120)) {
    callback(new Error('年龄范围应该大于一岁且小于等于120岁'))
  } else {
    callback()
  }
}

// 验证性别
var validateSex = (rule, value, callback) => {
  if (value === null) {
    callback(new Error('性别不允许为空'))
  } else {
    callback()
  }
}

// 验证不为空
var notEmpty = (rule, value, callback) => {
  if (value === '' || value === null || value === undefined) {
    callback(new Error('不允许为空'))
  } else {
    callback()
  }
}

export {
  validateName,
  validateAge,
  validateSex,
  notEmpty
}
