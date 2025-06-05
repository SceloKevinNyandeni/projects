package za.ac.cput.Factory;

import za.ac.cput.Domain.Book;
import za.ac.cput.Domain.Publisher;

import java.util.List;

public class PublisherFactory {
    public static Publisher createPublisher(Long publisherId,String publisherName, List<Book> books) {
        return new Publisher.PublisherBuilder()
                .setPublisherId(publisherId)
                .setPublisherName(publisherName)
                .setBooks(books)
                .build();
    }
}
