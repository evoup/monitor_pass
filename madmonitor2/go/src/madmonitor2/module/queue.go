package module

import "madmonitor2/inc"

func NewReadChannel(evictInterval int, dedupInterval int) *inc.ReaderChannel {
	return &inc.ReaderChannel{inc.ReaderQueue, 0, 0, evictInterval, dedupInterval}
}