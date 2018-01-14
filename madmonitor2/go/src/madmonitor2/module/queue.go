package module

import "madmonitor2/inc"

func NewReadChannel(evictInterval int, dedupInterval int) *inc.ReaderChannel {
	return &inc.ReaderChannel{inc.ReadQueue, 0, 0, evictInterval, dedupInterval}
}